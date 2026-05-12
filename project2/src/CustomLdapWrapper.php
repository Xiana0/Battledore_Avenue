<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Ldap\Exception\InvalidCredentialsException;
use Symfony\Component\Ldap\Exception\InvalidSearchCredentialsException;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\Adapter\EntryManagerInterface;

class CustomLdapWrapper implements LdapInterface
{

    public function __construct(
        private readonly LdapInterface $inner,
        private readonly RequestStack $requestStack,
        private readonly string $dnString,
        private readonly string $usernameParameter,
        private readonly string $passwordParameter,
        private readonly ?string $searchDn,
        #[\SensitiveParameter] private readonly ?string $searchPassword,
        private readonly bool $allowAnonymousBind = false,
    ) {}

    public function bind(?string $dn = null, #[\SensitiveParameter] ?string $password = null): void
    {
        // Case 1: explicitly provided DN and password
        if ($dn !== null && $dn !== '' && $password !== null && $password !== '') {
            try {
                $this->inner->bind($dn, $password);
                return;
            } catch (InvalidCredentialsException $e) {
                if ($dn === $this->searchDn && $password === $this->searchPassword) {
                    throw new InvalidSearchCredentialsException();
                }
                if (!$this->allowAnonymousBind) {
                    throw $e;
                }
            }
        } else {
            // Case 2: request-based bind
            $request = $this->requestStack->getCurrentRequest();
            if ($request !== null) {
                $username = $request->request->get($this->usernameParameter);
                $password = $request->request->get($this->passwordParameter);
                if ($username !== null && $username !== '' && $password !== null && $password !== '') {
                    $resolvedDn = str_replace('{user_identifier}', $username, $this->dnString);
                    try {
                        $this->inner->bind($resolvedDn, $password);
                        return;
                    } catch (InvalidCredentialsException $e) {
                        if (!$this->allowAnonymousBind) {
                            throw new BadCredentialsException($e->getMessage());
                        }
                    }
                }
            }
        }

        // Final fallback: anonymous bind (only if allowed)
        if (!$this->allowAnonymousBind) {
            throw new InvalidCredentialsException('Anonymous LDAP bind is disabled.');
        }
        $this->inner->bind(null, null);
    }

    public function escape(string $subject, string $ignore = '', int $flags = 0): string
    {
        return $this->inner->escape($subject, $ignore, $flags);
    }

    public function query(string $dn, string $query, array $options = []): QueryInterface
    {
        return $this->inner->query($dn, $query, $options);
    }

    public function getEntryManager(): EntryManagerInterface
    {
        return $this->inner->getEntryManager();
    }
}
