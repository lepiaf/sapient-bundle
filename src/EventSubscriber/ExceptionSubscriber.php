<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\EventSubscriber;

use lepiaf\SapientBundle\Exception\NoKeyFoundForRequesterException;
use lepiaf\SapientBundle\Exception\RequesterHeaderMissingException;
use lepiaf\SapientBundle\Exception\SignerHeaderMissingException;
use lepiaf\SapientBundle\Exception\VerifyRequestException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'handleException',
        ];
    }

    public function handleException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();
        if ($exception instanceof NoKeyFoundForRequesterException
            || $exception instanceof RequesterHeaderMissingException
            || $exception instanceof SignerHeaderMissingException
            || $exception instanceof VerifyRequestException
        ) {
            $event->setException(new BadRequestHttpException($exception->getMessage()));
            $event->stopPropagation();
        }
    }
}
