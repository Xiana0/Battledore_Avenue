<?php

namespace PHPMaker2026\Project1;

/**
 * Two Factor Authentication interface
 */
interface TwoFactorAuthenticationInterface
{
    /**
     * Get 2FA type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Check code
     *
     * @param string $secret Secret / One time password
     * @param string $code Code
     * @return bool
     */
    public static function checkCode(string $secret, string $code): bool;

    /**
     * Generate secret
     *
     * @return string
     */
    public static function generateSecret(): string;

    /**
     * Show (API action)
     *
     * @param string $user User
     * @return array
     */
    public function show(string $user): array;

    /**
     * Generate backup codes
     *
     * @return array
     */
    public static function generateBackupCodes(): array;

    /**
     * Get backup codes (API action)
     *
     * @param string $user User
     * @return array
     */
    public function getBackupCodes(string $user): array;

    /**
     * Get new backup codes (API action)
     *
     * @param string $user User
     * @return array
     */
    public function getNewBackupCodes(string $user): array;

    /**
     * Verify (API action)
     *
     * @param string $user
     * @param ?string $code
     * @return void
     */
    public function verify(string $user, ?string $code): array;

    /**
     * Reset (API action)
     *
     * @param string $user
     * @return void
     */
    public function reset(string $user): array;
}
