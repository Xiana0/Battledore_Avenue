<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Notifier\NotifierInterface;
use stdClass;
use Exception;
use InvalidArgumentException;

/**
 * Abstract Two Factor Authentication class
 */
abstract class AbstractTwoFactorAuthentication implements TwoFactorAuthenticationInterface
{
    public const TYPE = "";

    /**
     * Constructor
     */
    public function __construct(
        public UserProfile $profile,
        public Language $language,
        public NotifierInterface $notifier,
    ) {
    }

    /**
     * Get type
     */
    public function getType(): string
    {
        return static::TYPE;
    }

    /**
     * Check code
     *
     * @param string $secret Secret
     * @param string $code Code
     */
    abstract public static function checkCode(string $secret, string $code): bool;

    /**
     * Generate secret
     */
    abstract public static function generateSecret(): string;

    /**
     * Show (API action)
     *
     * @param string $user User
     * @return void
     */
    abstract public function show(string $user): array;

    /**
     * Generate backup codes
     */
    public static function generateBackupCodes(): array
    {
        $length = Config("TWO_FACTOR_AUTHENTICATION_BACKUP_CODE_LENGTH");
        $count = Config("TWO_FACTOR_AUTHENTICATION_BACKUP_CODE_COUNT");
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = Random($length);
        }
        return $codes;
    }

    /**
     * Load message from template name
     *
     * @param string $template Template name
     * @param ?string $langId Language ID
     * @param array $data Data for template
     * @return ParsedMessage
     */
    public function load(string $template, ?string $langId = null, array $data = []): ParsedMessage
    {
        $langId ??= $this->language->LanguageId;
        $parts = pathinfo($template);
        $finder = Finder::create()->files()->in(Config("LANGUAGE_FOLDER"))->name($parts["filename"] . "." . $langId . "." . $parts["extension"]); // Template for the language ID
        if (!$finder->hasResults()) {
            $finder->files()->name($parts["filename"]  . ".en-US." . $parts["extension"]); // Fallback to en-US
        }
        if (!$finder->hasResults()) {
            throw new Exception("Failed to load notification template '" . $template . "' for language '" . $langId . "'");
        }
        $content = "";
        $view = Container("notification.view");
        foreach ($finder as $file) {
            $content = $view->fetchTemplate($file->getFileName(), $data);
        }
        return ParseMessageTemplate($content);
    }

    /**
     * Check account
     *
     * @param ?string $account Account
     * @return ?string
     */
    public function checkAccount(?string $account): ?string
    {
        $masked = str_contains($account ?? "", "*");
        $verifiedAccount = $this->profile->getAccount($this->getType(), true);
        $unverifiedAccount = $this->profile->getAccount($this->getType(), false);
        if ($account && !$masked && $verifiedAccount === null) { // No verified acccount => configuring
            return $account;
        } elseif ($account === null && $verifiedAccount) { // Has verified account, use it
            return $verifiedAccount;
        } elseif ($account) { // Partially hidden
            if ($verifiedAccount && ($masked && PartialHide($verifiedAccount) == $account || !$masked && $verifiedAccount == $account)) { // Check if it is verified account first
                return $verifiedAccount;
            } elseif ($unverifiedAccount && ($masked && PartialHide($unverifiedAccount) == $account || !$masked && $unverifiedAccount == $account)) { // Check if it is unverified account
                return $unverifiedAccount;
            }
        }
        return null;
    }

    /**
     * Check if user name is valid
     *
     * @param string $user User
     * @return bool
     */
    public function isValidUser(string $user): bool
    {
        return !IsEmpty($user) && $this->profile->getUserName() == $user;
    }

    /**
     * Get backup codes (API action)
     *
     * @param string $user User
     * @return array
     */
    public function getBackupCodes(string $user): array
    {
        if ($this->isValidUser($user)) {
            $codes = $this->profile->getBackupCodes();
            return ["codes" => $codes, "success" => is_array($codes)];
        }
        return ["success" => false];
    }

    /**
     * Get new backup codes (API action)
     *
     * @param string $user User
     * @return array
     */
    public function getNewBackupCodes(string $user): array
    {
        if ($this->isValidUser($user)) {
            $codes = $this->profile->getNewBackupCodes();
            return ["codes" => $codes, "success" => is_array($codes)];
        }
        return ["success" => false];
    }

    /**
     * Verify (API action)
     *
     * @param string $user
     * @param ?string $code
     * @return array
     */
    public function verify(string $user, ?string $code): array
    {
        if ($this->isValidUser($user)) {
            $authType = $this->getType();
            if ($code === null) { // Verify if user has secret only
                if ($this->profile->hasUserSecret(true, $authType)) {
                    return ["success" => true];
                }
            } else { // Verify user code
                if ($this->profile->hasUserSecret(false, $authType)) {
                    return ["success" => $this->profile->verify2FACode($code, $authType)];
                }
            }
        }
        return ["success" => false];
    }

    /**
     * Reset (API action)
     *
     * @param string $user
     * @return array
     */
    public function reset(string $user): array
    {
        if ($this->isValidUser($user)) {
            $this->profile->setSecret($this->getType(), null);
            return ["success" => true];
        }
        return ["success" => false];
    }

    /**
     * Reset all secrets (API action)
     *
     * @param string $user
     * @return array
     */
    public function resetAll(string $user): array
    {
        if ($this->isValidUser($user)) {
            $this->profile->resetSecrets();
            return ["success" => true];
        }
        return ["success" => false];
    }
}
