<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Exception\EmptyBodyException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EmptyBodySubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                "handleEmptyBody", EventPriorities::POST_DESERIALIZE
            ]
        ];
    }

    /**
     * @throws EmptyBodyException
     */
    public function handleEmptyBody(RequestEvent $event): void
    {
        $method = $event->getRequest()->getMethod();
        if(!in_array($method, [Request::METHOD_POST, Request::METHOD_PUT])){
            return;
        }
        $data = $event->getRequest()->get('data');
        if ($data === null) {
            throw new EmptyBodyException();
        }
    }
}