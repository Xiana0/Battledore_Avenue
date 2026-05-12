<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;
use Throwable;

/**
 * Two Factor Authentication for email Authentication
 */
class EmailTwoFactorAuthentication extends AbstractTwoFactorAuthentication implements SendOneTimePasswordInterface
{
    public const TYPE = 'email';

    /**
     * Send one time password
     *
     * @param string $user User name
     * @param ?string $account User email
     */
    public function sendOneTimePassword(string $user, #[\SensitiveParameter] ?string $account = null): bool|string
    {
        // Get email address
        $email = $this->checkAccount($account);
        if (IsEmpty($email) || !CheckEmail($email)) { // Check if valid email address
            return sprintf($this->language->phrase('SendOtpSkipped'), $account, $user); // Return error message
        }

        // Check if OTP already sent
        $secret = $this->profile->getSecret('email'); // Get secret
        if (($secret?->otpCreatedAt ?? 0) && (time() - $secret->otpCreatedAt) < Config('RESEND_OTP_INTERVAL')) {
            return $this->language->phrase('SendOtpAgainLater');
        }

        // Create OTP
        $code = Random(Config('TWO_FACTOR_AUTHENTICATION_PASS_CODE_LENGTH'));

        // Get OTP email template
        $obj = $this->load(Config('EMAIL_ONE_TIME_PASSWORD_TEMPLATE'), data: [
            'Code' => $code,
            'Account' => $user,
            'ExpiryTime' => floor(Config('TWO_FACTOR_AUTHENTICATION_OTP_VALIDITY_PERIOD') / 60) // Convert seconds to minutes
        ]);
        $notification = (new EmailNotification($obj->subject))->setTemplate($obj); // Set email template
        $recipient = new Recipient(email: $email);

        // Call Otp_Sending event
        if (Otp_Sending($notification, $recipient)) {
            try {
                $this->notifier->send($notification, $recipient);
                // Save OTP and sent time in user profile
                $userSecret = $this->profile->getUserSecret('email'); // Get user secret
                $encryptedCode = Encrypt($code, $userSecret); // Encrypt OTP
                $this->profile->setOneTimePassword('email', $email, $encryptedCode);
                return true; // Return success
            } catch (Throwable $e) {
                return $e->getMessage(); // Return error message
            }
        } else {
            return $this->language->phrase('SendOtpCancelled'); // User cancel
        }
    }

    /**
     * Check code
     *
     * @param string $otp One time password
     * @param string $code Code
     */
    public static function checkCode(string $otp, string $code): bool
    {
        return $otp == $code;
    }

    /**
     * Generate secret
     */
    public static function generateSecret(): string
    {
        return Random(); // Generate a radom number for secret, used for encrypting OTP
    }

    /**
     * Get user email address
     *
     * @param string $user User name
     * @return void
     */
    public function show(string $user): array
    {
        if ($this->isValidUser($user)) {
            $email = $this->profile->getEmail(true); // Get verified email
            if (!IsEmpty($email)) {
                return ['account' => $email, 'success' => true, 'verified' => true];
            }
            $email = $this->profile->getEmail(false); // Get unverified email
            return ['account' => $email, 'success' => true, 'verified' => false];
        }
        return ['success' => false, 'error' => 'Missing user identifier'];
    }
}
