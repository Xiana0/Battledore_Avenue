<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Mime\Email;
use function Symfony\Component\String\s;

class EmailNotification extends Notification implements EmailNotificationInterface
{
    protected ?object $template;

    public function __construct(string $subject = '')
    {
        parent::__construct($subject, ['email']);
    }

    public function getTemplate(): ?object
    {
        return $this->template;
    }

    public function setTemplate(?object $template): static
    {
        $this->template = $template;
        return $this;
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, ?string $transport = null): ?EmailMessage
    {
        $email = (new Email())
            ->from($this->template?->from ?? Config('SENDER_EMAIL'))
            ->to($recipient->getEmail())
            ->subject($this->getSubject());
        $mailContent = $this->template?->content ?? $this->getContent();
        if (strtolower($this->template?->format ?? '') == 'html') {
            $email->html($mailContent);
        } else {
            if (s($mailContent)->match('/<\s*[a-z][^>]*>/i') !== null) { // Contains HTML tags
                $email->text(HtmlToText($mailContent));
            } else {
                $email->text($mailContent);
            }
        }
        return new EmailMessage($email);
    }
}
