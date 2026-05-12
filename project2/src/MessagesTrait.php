<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
trait MessagesTrait
{
    // Flash bag
    private ?FlashBagInterface $flashBag = null;

    // Use JavaScript message
    public ?bool $UseJavaScriptMessage = null;

    // Get flash bag
    public function getFlashBag(): FlashBagInterface
    {
        return $this->flashBag ??= FlashBag();
    }

    // Peek info message
    public function peekInfoMessage(): array
    {
        return $this->getFlashBag()->peek("info");
    }

    // Get info message
    public function getInfoMessage(): string
    {
        return implode("<br>", $this->getFlashBag()->get("info") ?? []);
    }

    // Set info message
    public function setInfoMessage(string|array $msg): void
    {
        $this->getFlashBag()->set("info", $msg);
    }

    // Add info message
    public function addInfoMessage(mixed $msg): void
    {
        $this->getFlashBag()->add("info", $msg);
    }

    // Peek failure message
    public function peekFailureMessage(): array
    {
        return $this->getFlashBag()->peek("danger");
    }

    // Get failure message
    public function getFailureMessage(): string
    {
        return implode("<br>", $this->getFlashBag()->get("danger") ?? []);
    }

    // Set failure message
    public function setFailureMessage(string|array $msg): void
    {
        $this->getFlashBag()->set("danger", $msg);
    }

    // Add failure message
    public function addFailureMessage(mixed $msg): void
    {
        $flashBag = $this->getFlashBag();
        $existingMessages = $flashBag->peek("danger");
        if (empty($existingMessages) || end($existingMessages) !== $msg) {
            $flashBag->add("danger", $msg);
        }
    }

    // Peek success message
    public function peekSuccessMessage(): array
    {
        return $this->getFlashBag()->peek("success");
    }

    // Get success message
    public function getSuccessMessage(): string
    {
        return implode("<br>", $this->getFlashBag()->get("success") ?? []);
    }

    // Set success message
    public function setSuccessMessage(string|array $msg): void
    {
        $this->getFlashBag()->set("success", $msg);
    }

    // Add success message
    public function addSuccessMessage(mixed $msg): void
    {
        $this->getFlashBag()->add("success", $msg);
    }

    // Peek warning message
    public function peekWarningMessage(): array
    {
        return $this->getFlashBag()->peek("warning");
    }

    // Get warning message
    public function getWarningMessage(): string
    {
        return implode("<br>", $this->getFlashBag()->get("warning") ?? []);
    }

    // Set warning message
    public function setWarningMessage(string|array $msg): void
    {
        $this->getFlashBag()->set("warning", $msg);
    }

    // Add warning message
    public function addWarningMessage(mixed $msg): void
    {
        $this->getFlashBag()->add("warning", $msg);
    }

    // Peek message heading
    public function peekMessageHeading(): array
    {
        return $this->getFlashBag()->peek("heading");
    }

    // Get message heading
    public function getMessageHeading(): string
    {
        return implode("<br>", $this->getFlashBag()->get("heading") ?? []);
    }

    // Set message heading
    public function setMessageHeading(mixed $msg): void
    {
        $this->getFlashBag()->set("heading", $msg);
    }

    // Get browser notifications
    public function getBrowserNotifications(): array
    {
        return $this->getFlashBag()->get("notification") ?? [];
    }

    // Clear all messages
    public function clearMessages(): mixed
    {
        return $this->getFlashBag()->clear();
    }

    // Get HTML message
    public function getHtmlMessage(): string
    {
        $hidden = $this->UseJavaScriptMessage ?? Config("USE_JAVASCRIPT_MESSAGE");
        $html = "";
        // Message heading
        $heading = fn() => ($h = $this->getMessageHeading()) ? '<h5 class="alert-heading">' . $h . '</h5>' : '';
        // Message showing
        $messageShowing = function($msg, $type = "") {
            $message = $msg;
            if (method_exists($this, "messageShowing")) {
                $this->messageShowing($message, $type);
            };
            return $message;
        };
        // Info message
        if ($message = $messageShowing($this->getInfoMessage(), "info")) {
            $html .= '<div class="alert alert-info alert-dismissible ew-info">' . $heading() . '<i class="icon fa-solid fa-info"></i>' . $message . '</div>';
        }
        // Warning message
        if ($warningMessage = $messageShowing($this->getWarningMessage(), "warning")) {
            $html .= '<div class="alert alert-warning alert-dismissible ew-warning">' . $heading() . '<i class="icon fa-solid fa-triangle-exclamation"></i>' . $warningMessage . '</div>';
        }
        // Success message
        if ($successMessage = $messageShowing($this->getSuccessMessage(), "success")) {
            $html .= '<div class="alert alert-success alert-dismissible ew-success">' . $heading() . '<i class="icon fa-solid fa-check"></i>' . $successMessage . '</div>';
        }
        // Failure message
        if ($errorMessage = $messageShowing($this->getFailureMessage(), "danger")) {
            $html .= '<div class="alert alert-danger alert-dismissible ew-error">' . $heading() . '<i class="icon fa-solid fa-ban"></i>' . $errorMessage . '</div>';
        }
        if ($html && !$hidden) {
            $html = '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="' . Language()->phrase("CloseBtn") . '"></button>' . $html;
        }
        return '<div class="ew-message-dialog' . ($hidden ? ' d-none' : '') . '">' . $html . '</div>';
    }

    // Get message as array
    public function getMessages(): array
    {
        $messages = [];
        // Message heading
        if ($heading = $this->getMessageHeading()) {
            $messages["heading"] = $heading;
        }
        // Info message
        if ($message = $this->getInfoMessage()) {
            $messages["infoMessage"] = $message;
        }
        // Warning message
        if ($warningMessage = $this->getWarningMessage()) {
            $messages["warningMessage"] = $warningMessage;
        }
        // Success message
        if ($successMessage = $this->getSuccessMessage()) {
            $messages["success"] = true;
            $messages["successMessage"] = $successMessage;
        }
        // Failure message
        if ($failureMessage = $this->getFailureMessage()) {
            $messages["failureMessage"] = $failureMessage;
        }
        return $messages;
    }
}
