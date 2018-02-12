<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\EventSubscriber;

use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SealingPublicKey;
use ParagonIE\Sapient\Sapient;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SealResponseSubscriber implements EventSubscriberInterface
{
    /**
     * @var HttpFoundationFactory
     */
    private $httpFoundationFactory;

    /**
     * @var string
     */
    private $serverSealPublic;

    /**
     * @var Sapient
     */
    private $sapient;

    /**
     * @var DiactorosFactory
     */
    private $diactorosFactory;

    public function __construct(HttpFoundationFactory $httpFoundationFactory, DiactorosFactory $diactorosFactory, Sapient $sapient)
    {
        $this->httpFoundationFactory = $httpFoundationFactory;
        $this->diactorosFactory = $diactorosFactory;
        $this->sapient = $sapient;
        $this->serverSealPublic = 'W28Us4xiuXv9B77Z0Ck4AHWyiwl5g51297_oSfNQ_lw=';
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['sealPsrResponse', -110],
            KernelEvents::RESPONSE => ['sealHttpFoundationResponse', -110],
        ];
    }

    public function sealHttpFoundationResponse(FilterResponseEvent $event): void
    {
        $event->setResponse(
            $this->sealResponse($this->diactorosFactory->createResponse($event->getResponse()))
        );
    }

    public function sealPsrResponse(GetResponseForControllerResultEvent $event): void
    {
        $response = $event->getResponse();
        if (!$response instanceof ResponseInterface) {
            return;
        }

        $event->setResponse($this->sealResponse($response));
    }

    private function sealResponse(ResponseInterface $response): Response
    {
        $psrResponse = $this->sapient->sealResponse($response, new SealingPublicKey(Base64UrlSafe::decode($this->serverSealPublic)));

        return $this->httpFoundationFactory->createResponse($psrResponse);
    }
}
