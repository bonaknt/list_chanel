<?php

namespace App\Service;

use App\Entity\Token;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class TokenSendler
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendToken(Token $token)
    {
        $email = ((new TemplatedEmail())
            ->from('hello@example.com')
            ->to($token->getUser()->getEmail())
            ->subject('registration mail')
            ->htmlTemplate('emails/registration.html.twig')
            ->context(['token' => $token->getValue()]));

        $this->mailer->send($email);
    }
}