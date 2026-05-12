<?php

namespace PHPMaker2026\Project1;

/**
 * Captcha base class
 */
class BaseCaptcha implements CaptchaInterface
{
    public string $ErrorMessage = "";
    public string $ResponseField = "";
    public string $Response = "";

    // Get element name
    public function getElementName(): string
    {
        return $this->ResponseField;
    }

    // Get element ID
    public function getElementId(): string
    {
        $id = $this->ResponseField;
        $pageId = CurrentPageID();
        if ($id != "" && $pageId != "") {
            $id .= "-" . $pageId;
        }
        return $id;
    }

    // Get Session Name
    public function getSessionName(?string $pageId = null): string
    {
        $name = SESSION_CAPTCHA_CODE;
        $pageId ??= Route("page") ?? CurrentPageID();
        if ($pageId != "") {
            $name .= "_" . $pageId;
        }
        return $name;
    }

    // HTML tag
    public function getHtml(): string
    {
        return "";
    }

    // HTML tag for confirm page
    public function getConfirmHtml(): string
    {
        return "";
    }

    // Validate
    public function validate(): bool
    {
        return true;
    }

    // Client side validation script
    public function getScript(): string
    {
        return "";
    }

    // Get error message
    public function getErrorMessage(): string
    {
        return $this->ErrorMessage;
    }

    // Set error message
    public function setErrorMessage(string $msg): void
    {
        $this->ErrorMessage = $msg;
    }

    // Set default error message
    public function setDefaultErrorMessage(): void
    {
        $this->ErrorMessage = Language()->phrase(IsEmpty($this->Response) ? "EnterValidateCode" : "IncorrectValidationCode");
    }
}
