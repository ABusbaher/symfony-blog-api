<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordHashSubscriber implements EventSubscriberInterface
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {

    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                'hashPassword', EventPriorities::PRE_WRITE
            ]
        ];
    }

    public function hashPassword(ViewEvent $event)
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if(!$user instanceof User || Request::METHOD_POST !== $method){
            return null;
        }
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $user->getPassword())
        );
    }
}