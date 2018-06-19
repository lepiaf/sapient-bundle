<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\EventSubscriber;

use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SealingSecretKey;
use ParagonIE\Sapient\Sapient;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UnsealRequestSubscriber implements EventSubscriberInterface
{
    /**
     * @var Sapient
     */
    private $sapient;

    /**
     * @var DiactorosFactory
     */
    private $diactorosFactory;

    /**
     * @var string
     */
    private $privateKey;

    public function __construct(Sapient $sapient, DiactorosFactory $diactorosFactory, string $privateKey)
    {
        $this->sapient = $sapient;
        $this->diactorosFactory = $diactorosFactory;
        $this->privateKey = $privateKey;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['unsealRequest', -100],
        ];
    }

    public function unsealRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();
        $psrRequest = $this->diactorosFactory->createRequest($request);
        $unsealedPsrRequest = $this->sapient->unsealRequest(
            $psrRequest,
            new SealingSecretKey(Base64UrlSafe::decode($this->privateKey))
        );

        $request->initialize(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            (string) $unsealedPsrRequest->getBody()
        );
    }
}
