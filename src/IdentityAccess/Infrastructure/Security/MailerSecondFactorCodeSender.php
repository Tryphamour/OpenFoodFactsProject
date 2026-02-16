<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Application\Port\SecondFactorCodeSender;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class MailerSecondFactorCodeSender implements SecondFactorCodeSender
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function send(string $email, string $code): void
    {
        $message = (new Email())
            ->from('no-reply@openfoodfacts.local')
            ->to($email)
            ->subject('Your login verification code')
            ->text(sprintf('Your verification code is: %s', $code));

        $this->mailer->send($message);
    }
}

