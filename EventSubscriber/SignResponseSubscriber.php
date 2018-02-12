<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\EventSubscriber;

use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SigningSecretKey;
use ParagonIE\Sapient\Sapient;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SignResponseSubscriber implements EventSubscriberInterface
{
    /**
     * @var HttpFoundationFactory
     */
    private $httpFoundationFactory;

    /**
     * @var string
     */
    private $serverSignSecret;

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
        $this->serverSignSecret = '';
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => 'signPsrResponse',
            KernelEvents::RESPONSE => 'signHttpFoundationResponse',
        ];
    }

    public function signHttpFoundationResponse(FilterResponseEvent $event): void
    {
        $event->setResponse(
            $this->signResponse($this->diactorosFactory->createResponse($event->getResponse()))
        );
    }

    public function signPsrResponse(GetResponseForControllerResultEvent $event): void
    {
        $response = $event->getResponse();
        if (!$response instanceof ResponseInterface) {
            return;
        }

        $event->setResponse($this->signResponse($response));
    }

    private function signResponse(ResponseInterface $response): Response
    {
        $psrResponse = $this->sapient->signResponse($response, new SigningSecretKey(Base64UrlSafe::decode($this->serverSignSecret)));

        return $this->httpFoundationFactory->createResponse($psrResponse);
    }
}
