<?php

namespace PHPMaker2026\Project1;

use PragmaRX\Google2FA\Google2FA;
use Com\Tecnick\Barcode\Barcode;

/**
 * Two Factor Authentication class (Google Authenticator only)
 */
class PragmaRxTwoFactorAuthentication extends AbstractTwoFactorAuthentication implements TwoFactorAuthenticationInterface
{
    public const TYPE = "google";

    /**
     * Get Google2FA
     *
     * @return Google2FA
     */
    public static function getGoogle2FA(): Google2FA
    {
        $google2fa = (new Google2FA())->setEnforceGoogleAuthenticatorCompatibility(true);
        $google2fa->setWindow(Config("TWO_FACTOR_AUTHENTICATION_DISCREPANCY"));
        $google2fa->setOneTimePasswordLength(Config("TWO_FACTOR_AUTHENTICATION_PASS_CODE_LENGTH"));
        return $google2fa;
    }

    /**
     * Get QR Code URL
     *
     * @param string $user User name
     * @param string $secret Secret
     * @return string URL
     */
    public static function getQrCodeUrl(string $user, string $secret): string
    {
        $issuer = Config("TWO_FACTOR_AUTHENTICATION_ISSUER");
        $size = Config("TWO_FACTOR_AUTHENTICATION_QRCODE_SIZE");
        $url = self::getGoogle2FA()->getQRCodeUrl($issuer, $user, $secret);
        $barcode = new Barcode();
        $bobj = $barcode->getBarcodeObj(
            "QRCODE,H", // Barcode type and additional comma-separated parameters
            $url, // Data string to encode
            $size, // Width (use absolute or negative value as multiplication factor)
            $size, // Height (use absolute or negative value as multiplication factor)
            "black", // Foreground color
            [-2, -2, -2, -2] // Padding (use absolute or negative values as multiplication factors)
        )->setBackgroundColor("white"); // Background color
        return "data:image/png;base64," . base64_encode($bobj->getPngData());
    }

    /**
     * Check code
     *
     * @param string $secret Secret
     * @param string $code Code
     * @return bool
     */
    public static function checkCode(string $secret, string $code): bool
    {
        return self::getGoogle2FA()->verifyKey($secret, $code);
    }

    /**
     * Generate secret
     */
    public static function generateSecret(): string
    {
        return self::getGoogle2FA()->generateSecretKey();
    }

    /**
     * Get QR Code URL
     *
     * @param string $user User
     * @return array
     */
    public function show(string $user): array
    {
        if ($this->isValidUser($user)) {
            if (!$this->profile->hasUserSecret(true, "google")) {
                $secret = $this->profile->getUserSecret("google"); // Get Secret
                return ["url" => self::getQrCodeUrl($user, $secret), "success" => true];
            }
        }
        return ["success" => false];
    }
}
