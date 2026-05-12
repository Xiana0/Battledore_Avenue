<?php

namespace PHPMaker2026\Project1;

use Defuse\Crypto\Key;
use Defuse\Crypto\KeyProtectedByPassword;
use Defuse\Crypto\Crypto;

/**
 * Class for encryption/decryption with php-encryption
 */
class PhpEncryption
{
    protected Key $Key;

    // Constructor
    public function __construct(string $encodedKey, string $password = "")
    {
        if ($password) { // Password protected key
            $key = KeyProtectedByPassword::loadFromAsciiSafeString($encodedKey);
            $this->Key = $key->unlockKey($password);
        } else { // Random key
            $this->Key = Key::loadFromAsciiSafeString($encodedKey);
        }
    }

    // Create random password protected key
    public static function createRandomPasswordProtectedKey(string $password): string
    {
        $protectedKey = KeyProtectedByPassword::createRandomPasswordProtectedKey($password);
        return $protectedKey->saveToAsciiSafeString();
    }

    // Create new random key without password
    public static function createNewRandomKey(): string
    {
        $key = Key::createNewRandomKey();
        return $key->saveToAsciiSafeString();
    }

    // Encrypt with password
    public static function encryptWithPassword(string $plaintext, string $password): string
    {
        return Crypto::encryptWithPassword($plaintext, $password);
    }

    // Decrypt with password
    public static function decryptWithPassword(string $plaintext, string $password): string
    {
        return Crypto::decryptWithPassword($plaintext, $password);
    }

    // Encrypt
    public function encrypt(string $plaintext): string
    {
        return Crypto::encrypt($plaintext, $this->Key);
    }

    // Decrypt
    public function decrypt(string $plaintext): string
    {
        return Crypto::decrypt($plaintext, $this->Key);
    }
}
