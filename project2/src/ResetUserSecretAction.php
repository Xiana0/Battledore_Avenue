<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\JsonResponse;
use PHPMaker2026\Project1\Entity as BaseEntity;

/**
 * Reset User Secret Action
 */
class ResetUserSecretAction extends ListAction
{
    // Constructor
    public function __construct(
        public string $Action = "resetusersecret",
        public string $Caption = "",
        public bool $Allowed = true,
        public ActionType $Method = ActionType::AJAX, // Postback (P) / Redirect (R) / Ajax (A)
        public ActionType $Select = ActionType::SINGLE, // Multiple (M) / Single (S) / Custom (C)
        public string|array $ConfirmMessage = "", // Message or Swal config
        public string $Icon = "fa-solid fa-star ew-icon", // Icon
        public string $Success = "", // JavaScript callback function name
        public mixed $Handler = null, // PHP callable to handle the action
        public string $SuccessMessage = "", // Default success message
        public string $FailureMessage = "", // Default failure message
    ) {
        $this->language = Language();
        $this->Caption = $this->language->phrase("ResetUserSecretBtn");
        $this->SuccessMessage = $this->language->phrase("ResetUserSecretSuccess");
        $this->FailureMessage = $this->language->phrase("ResetUserSecretFailure");
        $this->Allowed = IsAdmin();
    }

    // Set fields (override)
    public function setFields(DbFields $value): static
    {
        $this->reset();
        $this->fields = $value;
        $hasUserSecret = CreateProfile(
            $this->fields[Config("USERNAME_FIELD_NAME")]->DbValue,
            HtmlDecode($this->fields[Config("USER_PROFILE_FIELD_NAME")]->DbValue)
        )->hasUserSecret(true); // Create new user profile
        $this->setVisible($hasUserSecret);
        return $this;
    }

    // Handle the action
    public function handle(BaseEntity $row, PageInterface $listPage): bool
    {
        if ($listPage->TableName == Config("USER_TABLE_NAME")) {
            $userName = $row[Config("USERNAME_FIELD_NAME")];
            $result = CreateProfile($userName, HtmlDecode($row[Config("USER_PROFILE_FIELD_NAME")] ?? "")) // Create new user profile
                ->resetSecrets();
            if ($result) {
                $listPage->Response = new JsonResponse(["successMessage" => sprintf($this->SuccessMessage, $userName), "disabled" => true]); // Disable the button
            } else {
                $listPage->Response = new JsonResponse(["failureMessage" => sprintf($this->FailureMessage, $userName)]);
            }
            return $result;
        }
        return false;
    }
}
