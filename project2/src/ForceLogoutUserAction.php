<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\JsonResponse;
use PHPMaker2026\Project1\Entity as BaseEntity;

/**
 * Force Logout User Action
 */
class ForceLogoutUserAction extends ListAction
{
    // Constructor
    public function __construct(
        public string $Action = "forcelogoutuser",
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
        $this->Caption = $this->language->phrase("ForceLogoutUserBtn");
        $this->SuccessMessage = $this->language->phrase("ForceLogoutUserSuccess");
        $this->FailureMessage = $this->language->phrase("ForceLogoutUserFailure");
        $this->Allowed = IsAdmin();
    }

    // Set fields (override)
    public function setFields(DbFields $value): static
    {
        $this->reset();
        $this->fields = $value;
        $profile = CreateProfile(
            $this->fields[Config("USERNAME_FIELD_NAME")]->DbValue,
            HtmlDecode($this->fields[Config("USER_PROFILE_FIELD_NAME")]->DbValue)
        ); // Create new user profile
        $totalCount = $profile->activeUserSessionCount(false);
        $activeCount = $profile->activeUserSessionCount();
        if ($totalCount == 0 && $activeCount == 0) { // Do not show link if no active sessions
            $this->setVisible(false);
        } else {
            if ($profile->isForceLogout()) { // Being force logout
                $caption = $this->language->phrase("ForceLogoutInProgress");
                $title = HtmlTitle($caption);
                $this->setEnabled(false);
            }
            // Show active session count next to user name
            $message = sprintf($this->language->phrase("ActiveUserSessions"), $activeCount, $totalCount);
            $this->fields[Config("USERNAME_FIELD_NAME")]->ViewValue .= '<span class="badge rounded-pill text-bg-info ms-2">' . $message . '</span>';
        }
        return $this;
    }

    // Handle the action
    public function handle(BaseEntity $row, PageInterface $listPage): bool
    {
        if ($listPage->TableName == Config("USER_TABLE_NAME") && UserProfile::$FORCE_LOGOUT_USER_ENABLED) {
            $userName = $row[Config("USERNAME_FIELD_NAME")];
            $result = CreateProfile($userName, HtmlDecode($row[Config("USER_PROFILE_FIELD_NAME")] ?? "")) // Create new user profile
                ->forceLogoutUser();
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
