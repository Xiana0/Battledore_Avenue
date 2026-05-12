<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;

class EsmtpTransportFactoryDecorator implements TransportFactoryInterface
{

    public function __construct(
        protected readonly TransportFactoryInterface $inner,
        protected readonly array $settings,
        protected readonly iterable $authenticators = [],
    ) {}

    public function create(Dsn $dsn): TransportInterface
    {
        $raw = $this->settings['OPTIONS'] ?? [];
        $options = is_array($raw)
            ? $raw
            : (is_string($raw) ? (parse_str($raw, $out) || true ? $out : []) : []);
        $scheme = $dsn->getScheme();
        $user = $dsn->getUser() ?? '';
        $password = $dsn->getPassword() ?? '';
        $port = $dsn->getPort();
        $secure = strtolower($this->settings['SECURE_OPTION'] ?? '');

        // Handle secure modes
        switch ($secure) {
            case 'ssl':
                // Implicit TLS from the very start
                // $scheme = 'smtps';
                $port ??= 465;
                break;
            case 'tls':
                // STARTTLS (explicit upgrade)
                // Stay on 'smtp' but enforce TLS upgrade
                // $scheme = 'smtp';
                $port ??= 587;
                // $options['require_tls'] = 'true'; // This setting only applies when using the smtp:// protocol
                break;
            default:
                // Plain or opportunistic TLS
                // $scheme = 'smtp';
                $port ??= 25;
                break;
        }
        $dsn = new Dsn(
            $scheme,
            $dsn->getHost(),
            $user,
            $password,
            $port,
            $options
        );
        $transport = $this->inner->create($dsn);
        if (
            $transport instanceof EsmtpTransport &&
            method_exists($transport, 'setAuthenticators')
        ) {
            $authenticators = is_array($this->authenticators)
                ? $this->authenticators
                : iterator_to_array($this->authenticators);
            if (!empty($authenticators)) {
                $transport->setAuthenticators($authenticators);
            }
        }
        return $transport;
    }

    public function supports(Dsn $dsn): bool
    {
        return $this->inner->supports($dsn);
    }

    public function getSupportedSchemes(): array
    {
        return $this->inner->getSupportedSchemes();
    }
}
