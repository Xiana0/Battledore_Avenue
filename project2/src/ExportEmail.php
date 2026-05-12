<?php

namespace PHPMaker2026\Project1;

use DiDom\Document;
use DiDom\Element;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Export to email
 */
class ExportEmail extends AbstractExport
{
    public string $FileExtension = "html";
    public string $Disposition = "inline";

    // Table header
    public function exportTableHeader(): void
    {
        $this->Text .= "<table style=\"border-collapse: collapse;\">";
    }

    // Cell styles
    protected function cellStyles(DbField $fld): string
    {
        $styles = Config("EXPORT_TABLE_CELL_STYLES");
        if (is_array($styles)) {
            $style = array_reduce(array_keys($styles), fn($carry, $key) => $carry .= $key . ":" . $styles[$key] . ";", "");
            $fld->CellAttrs->prepend("style", $style, ";");
        }
        return $this->ExportStyles ? $fld->cellStyles() : "";
    }

    // Export field value
    public function exportFieldValue(DbField $fld): mixed
    {
        $exportValue = $fld->exportValue();
        if ($fld->ExportFieldImage && $fld->ViewTag == "IMAGE") {
            if ($fld->ImageResize) {
                $exportValue = GetFileImgTag($fld->getTempImage());
            } elseif ($fld->ExportHrefValue != "" && is_object($fld->Upload)) {
                if (!IsEmpty($fld->Upload->DbValue)) {
                    $exportValue = GetFileATag($fld, $fld->ExportHrefValue);
                }
            }
        } elseif ($fld->ExportFieldImage && $fld->ExportHrefValue != "") { // Export custom view tag, e.g. barcode
            $exportValue = GetFileImgTag($fld->ExportHrefValue);
        }
        return $exportValue;
    }

    /**
     * Add image to end of page
     *
     * @param string $imagefn Image file
     * @param ?string $break Break type (before/after/none)
     * @return void
     */
    #[\Override]
    public function addImage(string $imagefn, ?string $break = null): void
    {
        $classes = "ew-export";
        if (SameText($break, "before")) {
            $classes .= " break-before-page";
        } elseif (SameText($break, "after")) {
            $classes .= " break-after-page";
        } elseif (SameText($break, "none")) {
            $classes .= " break-after-avoid";
        }
        $html = '<div class="' . $classes . '">' . GetFileImgTag($imagefn) . "</div>";
        if (ContainsText($this->Text, "</body>")) {
            $this->Text = str_replace("</body>", $html . "</body>", $this->Text); // Insert before </body>
        } else {
            $this->Text .= $html; // Append to end
        }
    }

    /**
     * Add temp image to $httpContext["TempImages"]
     *
     * @param string $tmpimage Temp image file name
     * @return void
     */
    public function addTempImage(string $tmpimage): void
    {
        global $httpContext;
        $folder = UploadTempPath(true);
        $ext = array_find([".gif", ".jpg", ".png"], fn($e) => file_exists($folder . $tmpimage . $e));
        if ($ext) {
            $tmpimage .= $ext; // Add file extension
            if (!in_array($tmpimage, $httpContext["TempImages"])) { // Add to TempImages
                $httpContext->addTempImage($tmpimage);
            }
        }
    }

    /**
     * Get temp image as Base64 data URL
     *
     * @param string $tmpimage Temp image file name
     * @return string
     */
    public function getBase64Url(string $tmpimage): string
    {
        $folder = UploadTempPath(true);
        $ext = array_find([".gif", ".jpg", ".png"], fn($e) => file_exists($folder . $tmpimage . $e));
        return $ext ? ImageFileToBase64Url($folder . $tmpimage . $ext) : $tmpimage;
    }

    /**
     * Adjust src attribute of image tags
     *
     * @param string $html HTML
     * @return string HTML
     */
    public function adjustImage(string $html): string
    {
        $doc = $this->getDocument($html);
        $inline = $this->getDisposition() == "inline"; // Inline
        $images = $doc->find("img");
        foreach ($images as $image) {
            $src = $image->attr("src");
            if (StartsString("data:", $src) && ContainsString($src, ";base64,")) { // Data URL
                if ($inline) { // Inline (No change required if disposition is "attachment")
                    $image->attr("src", TempImage(DataFromBase64Url($src), true)); // Create temp image as cid URL
                }
            } else { // Not embedded image
                if (file_exists($src)) {
                    if ($inline) { // Inline
                        $image->attr("src", TempImage(file_get_contents($src), true)); // Create temp image as cid URL
                    } else { // Attachment
                        $image->attr("src", ImageFileToBase64Url($src)); // Replace image by data URL
                    }
                }
            }
        }
        return $doc->format()->html();
    }

    /**
     * Send email
     *
     * @param string $fileName File name of attachment
     * @return array Result
     */
    public function send(string $fileName): array
    {
        global $httpContext;
        $sender = Param("sender", "");
        $recipient = Param("recipient", "");
        $cc = Param("cc", "");
        $bcc = Param("bcc", "");
        $subject = Param("subject", "");
        $message = Param("message", "");
        $inline = $this->getDisposition() == "inline"; // Inline
        $content = $this->adjustImage($this->Text);

        // Send email
        $email = new Email();
        $email->Sender = $sender; // Sender
        $email->Recipient = $recipient; // Recipient
        $email->Cc = $cc; // Cc
        $email->Bcc = $bcc; // Bcc
        $email->Subject = $subject; // Subject
        $email->Format = "html";
        if ($message != "") {
            $message = RemoveXss($message) . "<br><br>";
        }
        $email->Content = $message;
        if ($inline) { // Inline
            foreach ($httpContext["TempImages"] as $tmpimage) {
                $email->addEmbeddedImage($tmpimage);
            }
            $email->Content .= $content;
        } else { // Attachment
            $email->addAttachment($fileName, $content);
        }
        $args = [];
        $emailSent = false;
        $tbl = $this->table;
        if (!method_exists($this->table, "emailSending") || $this->table->emailSending($email, $args)) {
            $emailSent = $email->send();
        }

        // Check email sent status
        if ($emailSent) {
            // Update email sent count
            Session(Config("EXPORT_EMAIL_COUNTER"), (Session(Config("EXPORT_EMAIL_COUNTER")) ?? 0) + 1);

            // Sent email success
            return ["success" => true, "message" => Language()->phrase("SendEmailSuccess")];
        } else {
            // Sent email failure
            return ["success" => false, "message" => !IsEmpty($email->LastError) ? $email->LastError : Language()->phrase("FailedToSendMail")];
        }
    }

    // Export
    public function export(string $fileName = "", bool $output = true, bool $save = false): Response
    {
        $this->adjustHtml();
        if ($save) { // Save to folder
            WriteFile(ExportPath() . $this->getSaveFileName(), $this->Text);
        }
        if ($output) { // Output
            return new JsonResponse($this->send($fileName)); // Send email
        }
        return $this->response;
    }

    // Destructor
    public function __destruct()
    {
    }
}
