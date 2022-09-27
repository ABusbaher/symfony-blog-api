<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Security\TokenGenerator;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use App\Email\Mailer;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegisterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private TokenGenerator $tokenGenerator,
        private Mailer $mailer
    )
    {

    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                'userRegistered', EventPriorities::PRE_WRITE
            ]
        ];
    }

    /**
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function userRegistered(ViewEvent $event): void
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$user instanceof User || $method != Request::METHOD_POST || !$user->getPassword()) {
            return;
        }

        // Hash a password
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $user->getPassword())
        );
        // Create confirmation token
        $user->setConfirmationToken($this->tokenGenerator->getRandomSecureToken());
        //Send email
        $this->mailer->sendConfirmationEmail($user);
    }
}