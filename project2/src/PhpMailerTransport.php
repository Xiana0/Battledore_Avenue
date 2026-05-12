<?php

namespace PHPMaker2026\Project1;

use Psr\Log\LoggerInterface;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Event\FailedMessageEvent;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\Event\SentMessageEvent;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\Email;
use Throwable;
use Exception;

/**
 * Sends emails by PHPMailer
 */
class PhpMailerTransport implements TransportInterface
{
    protected float $rate = 0;
    protected float $lastSent = 0;
    protected PHPMailer $mailer;

    public function __construct(
        protected string $host = 'localhost',
        protected int $port = 0,
        protected ?string $secure = null,
        protected ?EventDispatcherInterface $dispatcher = null,
        protected ?LoggerInterface $logger = null
    ) {
        $this->mailer = new PHPMailer(true); // Throw exceptions
        $this->mailer->Host = $host;
        $this->mailer->Port = $port;
        if ($secure === 'tls') {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($secure === 'ssl') {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }
        if ($logger) {
            $this->mailer->Debugoutput = $logger;
        }
    }

    public function getMailer(): PHPMailer
    {
        return $this->mailer;
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        $message = clone $message;
        $envelope = null !== $envelope ? clone $envelope : Envelope::create($message);
        try {
            if (!$this->dispatcher) {
                $sentMessage = new SentMessage($message, $envelope);
                $this->doSend($sentMessage);
                return $sentMessage;
            }
            $event = new MessageEvent($message, $envelope, (string) $this);
            $this->dispatcher->dispatch($event);
            if ($event->isRejected()) {
                return null;
            }
            $envelope = $event->getEnvelope();
            $message = $event->getMessage();
            $sentMessage = new SentMessage($message, $envelope);
            try {
                $this->doSend($sentMessage);
            } catch (Throwable $error) {
                $this->dispatcher->dispatch(new FailedMessageEvent($message, $error));
                $this->checkThrottling();
                throw $error;
            }
            $this->dispatcher->dispatch(new SentMessageEvent($sentMessage));
            return $sentMessage;
        } finally {
            $this->checkThrottling();
        }
    }

    private function checkThrottling(): void
    {
        if (0 == $this->rate) {
            return;
        }
        $sleep = (1 / $this->rate) - (microtime(true) - $this->lastSent);
        if (0 < $sleep) {
            $this->Debugoutput->debug(sprintf('Email transport "%s" sleeps for %.2f seconds', __CLASS__, $sleep));
            usleep((int) ($sleep * 1000000));
        }
        $this->lastSent = microtime(true);
    }

    protected function doSend(SentMessage $message): void
    {
        $original = $message->getOriginalMessage();
        $envelope = $message->getEnvelope();
        $this->mailer->clearAllRecipients();
        $langId = CurrentLanguageID(true, true) ?: 'en';
        if (!in_array($langId, ['pt_br', 'sr_latn', 'zh_cn'])) {
            $langId = explode('_', $langId)[0];
        }
        PHPMailer::setLanguage($langId);
        if ($original instanceof Email) {
            foreach ($original->getTo() as $to) {
                $this->mailer->addAddress($to->getAddress(), $to->getName());
            }
            foreach ($original->getCc() as $cc) {
                $this->mailer->addCc($cc->getAddress(), $cc->getName());
            }
            foreach ($original->getBcc() as $bcc) {
                $this->mailer->addBcc($bcc->getAddress(), $bcc->getName());
            }
            foreach ($original->getFrom() as $from) {
                $this->mailer->setFrom($from->getAddress(), $from->getName());
            }
        } else {
            foreach ($envelope->getRecipients() as $to) {
                $this->mailer->addAddress($to->getAddress(), $to->getName());
            }
            $from = $envelope->getSender();
            $this->mailer->setFrom($from->getAddress(), $from->getName());
        }
        if ($original instanceof Message) {
            $content = $original->getHeaders()->toString() . $original->getBody()?->toString();
            [$header, $body] = explode("\r\n\r\n", $content, 2) + ['', ''];
            $this->mailer->set('MIMEHeader', $header);
            $this->mailer->set('MIMEBody', $body);
        } elseif ($original instanceof RawMessage) {
            $this->mailer->set('MIMEHeader', '');
            $this->mailer->set('MIMEBody', $original->toString());
        }
        try {
            $this->mailer->postSend();
        } catch (Exception $e) {
            $this->mailer->set('mailHeader', ''); // mailHeader is protected property
            $errorMessage = $e->getMessage();
            $message->appendDebug($errorMessage);
            throw new TransportException($errorMessage);
        }
    }

    public function __toString(): string
    {
        $name = sprintf('phpmailer://%s', $this->host);
        $port = $this->port;
        if (!(25 === $port || $this->secure && 465 === $port)) {
            $name .= ':'.$port;
        }
        return $name;
    }
}
