<?php

namespace App\Email;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class Mailer
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendConfirmationEmail(User $user)
    {
        $email = (new TemplatedEmail())
            ->from('admin@a.com')
            ->to($user->getEmail())
            ->subject('Please confirm your account!')
            ->context([
                'user' => $user
            ])
            ->htmlTemplate('email/confirmation.html.twig');
        $this->mailer->send($email);
    }
}