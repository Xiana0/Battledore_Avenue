<?php

namespace PHPMaker2026\Project1;

/**
 * Switch User Action
 */
class SwitchUserAction extends ListAction
{
    // Constructor
    public function __construct(
        public string $Action = "switchuser",
        public string $Caption = "",
        public bool $Allowed = false,
        public ActionType $Method = ActionType::REDIRECT, // Postback (P) / Redirect (R) / Ajax (A)
        public ActionType $Select = ActionType::SINGLE, // Multiple (M) / Single (S) / Custom (C)
        public string|array $ConfirmMessage = "", // Message or Swal config
        public string $Icon = "fa-solid fa-star ew-icon", // Icon
        public string $Success = "", // JavaScript callback function name
        public mixed $Handler = null, // PHP callable to handle the action
        public string $SuccessMessage = "", // Default success message
        public string $FailureMessage = "", // Default failure message
    ) {
        $this->Caption = Language()->phrase("SwitchUser");
        $this->Allowed = Security()->canSwitchUser();
    }

    // Set fields (override)
    public function setFields(DbFields $value): static
    {
        $this->reset();
        $this->fields = $value;
        $username = $this->fields[Config("USERNAME_FIELD_NAME")]->DbValue;
        if ($username != CurrentUserName()) {
            $this->setData(Config("SECURITY.firewalls.main.switch_user.parameter"), $username);
        } else {
            $this->setVisible(false);
        }
        return $this;
    }

    // Get caption (override)
    public function getCaption(): string
    {
        $username = $this->getData(Config("SECURITY.firewalls.main.switch_user.parameter"));
        return sprintf($this->Caption ?: $this->Action, $username);
    }
}
