<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthoredEntitySubscriber implements EventSubscriberInterface
{
    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['getAuthenticatedUser', EventPriorities::PRE_WRITE]
        ];
    }

    public function getAuthenticatedUser(ViewEvent $event): void
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        /** @var User $author */
        $author = $this->tokenStorage->getToken()->getUser();
        if ((!$entity instanceof BlogPost && !$entity instanceof Comment) || Request::METHOD_POST !== $method) {
            return;
        }
        $entity->setAuthor($author);
    }
}