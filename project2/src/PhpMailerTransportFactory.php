<?php

namespace PHPMaker2026\Project1;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\OAuthTokenProvider;

class PhpMailerTransportFactory extends AbstractTransportFactory
{

    public function __construct(
        protected ?EventDispatcherInterface $dispatcher = null,
        protected ?HttpClientInterface $client = null,
        protected ?LoggerInterface $logger = null,
        protected readonly array $settings,
    ) {
        parent::__construct($dispatcher, $client, $logger);
    }

    public function create(Dsn $dsn): TransportInterface
    {
        $host = $dsn->getHost();
        $port = $dsn->getPort(0);
        $user = $dsn->getUser() ?? '';
        $password = $dsn->getPassword() ?? '';
        $secure = $this->settings['SECURE_OPTION'] ?? '';
        $transport = new PhpMailerTransport($host, $port, $secure, $this->dispatcher, $this->logger);
        $mailer = $transport->getMailer();
        $mailer->isSMTP();

        // Set up server settings
        $username = $dsn->getUser();
        $password = $dsn->getPassword();
        $mailer->SMTPAuth = $username != "" && $password != "";
        $mailer->Username = $user;
        $mailer->Password = $password;
        if (IsDebug()) {
            $mailer->SMTPDebug = 2; // DEBUG_SERVER
        }
        $options = $this->settings['OPTIONS'] ?? [];
        $options = is_array($options)
            ? $options
            : (is_string($options) ? (parse_str($options, $out) || true ? $out : []) : []);
        if (isset($options['auto_tls'])) {
            if (filter_var($options['auto_tls'], \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE) === false) {
                $mailer->SMTPAutoTLS = false;
            }
            unset($options['auto_tls']);
        }
        if (isset($options['verify_peer'])) {
            if (!filter_var($options['verify_peer'], \FILTER_VALIDATE_BOOL)) {
                $options['ssl']['verify_peer'] = false;
                $options['ssl']['verify_peer_name'] = false;
                $options['ssl']['allow_self_signed'] = true;
            }
            unset($options['verify_peer']);
        }
        if (isset($options['peer_fingerprint'])) {
            $options['ssl']['peer_fingerprint'] = $options['peer_fingerprint'];
            unset($options['peer_fingerprint']);
        }
        $mailer->SMTPOptions = $options;
        if (Config('PHPMAILER_OAUTH') instanceof OAuthTokenProvider) {
            $mailer->AuthType = 'XOAUTH2'; // Set AuthType to use XOAUTH2
            $mailer->setOAuth(Config('PHPMAILER_OAUTH'));
        }
        return $transport;
    }

    protected function getSupportedSchemes(): array
    {
        return ['phpmailer'];
    }
}
