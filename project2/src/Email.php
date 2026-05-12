<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Mime\Address;
use Exception;

/**
 * Email class
 */
class Email
{
    protected array $data = [];
    public string $LanguageFolder;
    public string $LanguageId;
    public string $LastError = ""; // Last error message

    // Constructor
    public function __construct(
        public string $Sender = "", // Sender
        public string $Recipient = "", // Recipient
        public string $Cc = "", // Cc
        public string $Bcc = "", // Bcc
        public string $Content = "", // Content
        public string $Format = "", // Format
        public string $Subject = "", // Subject
        public string $Charset = EMAIL_CHARSET, // Charset
        public int|string $Priority = 3, // Priority
        public array $Attachments = [], // Attachments
        public array $EmbeddedImages = [] // Embedded image
    ) {
        global $httpContext;
        $this->LanguageFolder = Config("LANGUAGE_FOLDER");
        $this->LanguageId = $httpContext["CurrentLanguage"];
    }

    // Convert address to string
    protected function addressToString(string|Address $value, string $name = ""): string
    {
        return $value instanceof Address
            ? $value->toString()
            : ($name ? $value . " <" . $name . ">" : $value);
    }

    public function setLanguageFolder(string $value): static
    {
        $this->LanguageFolder = $value;
        return $this;
    }

    public function setSender(string|Address $value, string $name = ""): static
    {
        $this->Sender = $this->addressToString($value, $name);
        return $this;
    }

    public function setFrom(string|Address $value, string $name = ""): static
    {
        $this->Sender = $this->addressToString($value, $name);
        return $this;
    }

    public function setRecipient(string|Address $value, string $name = ""): static
    {
        $this->Recipient = $this->addressToString($value, $name);
        return $this;
    }

    public function setTo(string|Address $value, string $name = ""): static
    {
        $this->Recipient = $this->addressToString($value, $name);
        return $this;
    }

    public function setCc(string|Address $value, string $name = ""): static
    {
        $this->Cc = $this->addressToString($value, $name);
        return $this;
    }

    public function setBcc(string|Address $value, string $name = ""): static
    {
        $this->Bcc = $this->addressToString($value, $name);
        return $this;
    }

    public function setSubject(string $value): static
    {
        $this->Subject = $value;
        return $this;
    }

    public function setFormat(string $value): static
    {
        $this->Format = $value;
        return $this;
    }

    public function setContent(string $value): static
    {
        $this->Content = $value;
        return $this;
    }

    public function setPriority(string $value): static
    {
        $this->Priority = $value;
        return $this;
    }

    public function setAttachments(array $value): static
    {
        $this->Attachments = $value;
        return $this;
    }

    public function setEmbeddedImages(array $value): static
    {
        $this->EmbeddedImages = $value;
        return $this;
    }

    public function setCharset(string $value): static
    {
        $this->Charset = $value;
        return $this;
    }

    /**
     * Load message from template name
     *
     * @param string $name Template file name
     * @param string $langId Language ID
     * @param array $data Data for template
     */
    public function load(string $name, ?string $langId = null, array $data = []): static
    {
        $langId ??= $this->LanguageId;
        $this->data = $data;
        $parts = pathinfo($name);
        $finder = Finder::create()->files()->in($this->LanguageFolder)->name($parts["filename"] . "." . $langId . "." . $parts["extension"]); // Template for the language ID
        if (!$finder->hasResults()) {
            $finder->files()->name($parts["filename"]  . ".en-US." . $parts["extension"]); // Fallback to en-US
        }
        if ($finder->hasResults()) {
            $content = "";
            $view = Container("notification.view");
            foreach ($finder as $file) {
                $content = $view->fetchTemplate($file->getFileName(), $data);
            }
            $obj = ParseMessageTemplate($content); // Parse message template
            $this->Subject = trim($data["Subject"] ?? $obj->subject ?? "");
            $this->Sender = trim($data["From"] ?? $obj->from ?? Config("SENDER_EMAIL"));
            $this->Recipient = trim($data["To"] ?? $obj->to ?? "");
            $this->Cc = trim($obj->cc ?? "");
            $this->Bcc = trim($obj->bcc ?? "");
            $this->Format = trim($obj->format ?? "");
            $this->Content = trim($obj->content ?? "");
        } else {
            throw new Exception("Failed to load email template '{$name}' for language '{$langId}'");
        }
        return $this;
    }

    // Get template data
    public function getData(): array
    {
        return $this->data;
    }

    // Replace sender
    public function replaceSender(string|Address $sender, string $name = ""): static
    {
        $this->Sender = $this->addressToString($sender, $name);
        return $this;
    }

    // Replace recipient
    public function replaceRecipient(string|Address $recipient, string $name = ""): static
    {
        $this->addRecipient($this->addressToString($recipient, $name));
        return $this;
    }

    // Add recipient
    public function addRecipient(string|Address $recipient, string $name = ""): static
    {
        $this->Recipient = Concat($this->Recipient, $this->addressToString($recipient, $name), ";");
        return $this;
    }

    // Add cc email
    public function addCc(string|Address $cc, string $name = ""): static
    {
        $this->Cc = Concat($this->Cc, $this->addressToString($cc, $name), ";");
        return $this;
    }

    // Add bcc email
    public function addBcc(string|Address $bcc, string $name = ""): static
    {
        $this->Bcc = Concat($this->Bcc, $this->addressToString($bcc, $name), ";");
        return $this;
    }

    // Replace subject
    public function replaceSubject(string $subject): static
    {
        $this->Subject = $subject;
        return $this;
    }

    // Replace content
    public function replaceContent(string $find, string $replaceWith): static
    {
        $this->Content = str_replace($find, $replaceWith, $this->Content);
        return $this;
    }

    /**
     * Add attachment
     *
     * @param string $fileName Full file path (without $content) or file name (with $content)
     * @param string $content File content
     */
    public function addAttachment(string $fileName, string $content = ""): static
    {
        if ($fileName != "") {
            $this->Attachments[] = ["filename" => $fileName, "content" => $content];
        }
        return $this;
    }

    /**
     * Add embedded image
     *
     * @param string $image File name of image (in global upload folder)
     */
    public function addEmbeddedImage(string $image): static
    {
        if ($image != "") {
            $this->EmbeddedImages[] = $image;
        }
        return $this;
    }

    /**
     * Send email
     *
     * @return bool Whether email is sent successfully
     */
    public function send(): bool
    {
        // Reset
        $this->LastError = "";
        // Send
        $result = SendEmail(
            $this->Sender,
            $this->Recipient,
            $this->Cc,
            $this->Bcc,
            $this->Subject,
            $this->Content,
            $this->Format,
            $this->Charset,
            $this->Priority,
            $this->Attachments,
            $this->EmbeddedImages
        );
        if ($result === true) {
            return true;
        }
        // Error
        $this->LastError = $result;
        return false;
    }
}
