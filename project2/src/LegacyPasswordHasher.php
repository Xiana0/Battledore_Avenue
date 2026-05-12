<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Hautelook\Phpass\PasswordHash;
use DateTimeImmutable;
use LogicException;

class LegacyPasswordHasher implements PasswordHasherInterface
{

    public function __construct(
        protected Language $language,
        protected ?DateTimeImmutable $cutoffDate = null
    ) {
        $this->cutoffDate ??= Config('PASSWORD_MIGRATION_CUTOFF_DATE');
    }

    public function hash(string $plainPassword): string
    {
        throw new LogicException('Legacy password hasher should never be used to hash passwords.');
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        // Stop after cutoff date
        if ($this->cutoffDate && new DateTimeImmutable() >= $this->cutoffDate) {
            throw new CustomUserMessageAuthenticationException($this->language->phrase('PasswordOutdated'));
        }
        if (trim($plainPassword) === '') {
            return false;
        }

        // PHP password_hash()
        if (
            str_starts_with($hashedPassword, '$2y$') // Old versions used PASSWORD_DEFAULT (bcrypt)
            // || str_starts_with($hashedPassword, '$argon2i$')
            // || str_starts_with($hashedPassword, '$argon2id$')
        ) {
            return password_verify($plainPassword, $hashedPassword)
                || password_verify(strtolower($plainPassword), $hashedPassword);
        }

        // phpass
        if (str_starts_with($hashedPassword, '$P$') || str_starts_with($hashedPassword, '$H$')) {
            $ar = Config('PHPASS_ITERATION_COUNT_LOG2');
            foreach ($ar as $i) {
                $hasher = new PasswordHash($i, true);
                if ($hasher->checkPassword($plainPassword, $hashedPassword)) {
                    return true;
                }
            }
            return false;
        }

        // MD5
        if (preg_match('/^[a-f0-9]{32}$/i', $hashedPassword)) {
            return strcasecmp(md5($plainPassword), $hashedPassword) === 0
                || strcasecmp(md5(strtolower($plainPassword)), $hashedPassword) === 0;
        }

        // Plain password
        return strcasecmp($hashedPassword, $plainPassword) === 0;
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return true;
    }
}
