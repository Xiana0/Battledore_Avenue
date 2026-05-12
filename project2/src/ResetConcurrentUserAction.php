<?php

namespace PHPMaker2026\Project1;

use PHPMaker2026\Project1\Entity as BaseEntity;

/**
 * Reset Concurrent User Action
 */
class ResetConcurrentUserAction extends ListAction
{
    // Constructor
    public function __construct(
        public string $Action = "resetconcurrentuser",
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
        $this->Caption = $this->language->phrase("ResetConcurrentUserBtn");
        $this->SuccessMessage = $this->language->phrase("ResetConcurrentUserSuccess");
        $this->FailureMessage = $this->language->phrase("ResetConcurrentUserFailure");
        $this->Allowed = IsAdmin();
    }

    // Handle the action
    public function handle(BaseEntity $row, PageInterface $listPage): bool
    {
        if ($listPage->TableName == Config("USER_TABLE_NAME")) {
            $userName = $row[Config("USERNAME_FIELD_NAME")];
            return CreateProfile($userName, HtmlDecode($row[Config("USER_PROFILE_FIELD_NAME")] ?? "")) // Create new user profile
                ->resetConcurrentUser();
        }
        return false;
    }
}
