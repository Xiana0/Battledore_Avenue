<?php

namespace PHPMaker2026\Project1;

use PHPMaker2026\Project1\Entity as BaseEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Throwable;

/**
 * Reset login attempts action
 */
class ResetLoginAttemptsAction extends ListAction
{
    // Constructor
    public function __construct(
        public string $Action = 'resetloginattempts',
        public string $Caption = '',
        public bool $Allowed = true,
        public ActionType $Method = ActionType::AJAX, // Postback (P) / Redirect (R) / Ajax (A)
        public ActionType $Select = ActionType::SINGLE, // Multiple (M) / Single (S) / Custom (C)
        public string|array $ConfirmMessage = '', // Message or Swal config
        public string $Icon = 'fa-solid fa-star ew-icon', // Icon
        public string $Success = '', // JavaScript callback function name
        public mixed $Handler = null, // PHP callable to handle the action
        public string $SuccessMessage = '', // Default success message
        public string $FailureMessage = '', // Default failure message
    ) {
        $this->language = Language();
        $this->Allowed = IsAdmin();
        // Keep old phrase IDs for backward compatibility
        $this->Caption = $this->language->phrase('ResetLoginRetryBtn');
        $this->SuccessMessage = $this->language->phrase('ResetLoginRetrySuccess');
        $this->FailureMessage = $this->language->phrase('ResetLoginRetryFailure');
    }

    // Handle the action
    public function handle(BaseEntity $row, PageInterface $listPage): bool
    {
        if ($listPage->TableName != Config('USER_TABLE_NAME')) {
            return false;
        }
        $userName = $row[Config('USERNAME_FIELD_NAME')];
        $clientIp = CreateProfile($userName, HtmlDecode($row[Config('USER_PROFILE_FIELD_NAME')] ?? '')) // Create new user profile
            ->getLastFailedClientIp();
        try {
            $this->resetLimiterForUser($userName, $clientIp);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    // Reset limiter for user
    function resetLimiterForUser(string $username, ?string $ip = null): void
    {
        // Create a subrequest
        $subRequest = Request::create('/'); // path doesn’t matter

        // Set the username attribute so limiter can read it
        $subRequest->attributes->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        // Set the IP: either provided or current client IP
        $ip ??= $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $server = $subRequest->server->all();
        $server['REMOTE_ADDR'] = $ip;
        $subRequest->server->replace($server);

        // Reset the limiters
        ServiceLocator('app.login_rate_limiter')?->reset($subRequest);
    }
}
