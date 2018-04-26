<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\EventSubscriber;

use lepiaf\SapientBundle\Service\PublicKeyGetter;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SealingPublicKey;
use ParagonIE\Sapient\Sapient;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
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
     * @var PublicKeyGetter
     */
    private $publicKeyGetter;

    /**
     * @var Sapient
     */
    private $sapient;

    /**
     * @var DiactorosFactory
     */
    private $diactorosFactory;

    public function __construct(HttpFoundationFactory $httpFoundationFactory, DiactorosFactory $diactorosFactory, Sapient $sapient, PublicKeyGetter $publicKeyGetter)
    {
        $this->httpFoundationFactory = $httpFoundationFactory;
        $this->diactorosFactory = $diactorosFactory;
        $this->sapient = $sapient;
        $this->publicKeyGetter = $publicKeyGetter;
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
        $publicKey = $this->getPublicKeyByRequester($event->getRequest());
        $psrResponse = $this->diactorosFactory->createResponse($event->getResponse());
        $event->setResponse($this->sealResponse($psrResponse, $publicKey));
    }

    public function sealPsrResponse(GetResponseForControllerResultEvent $event): void
    {
        $publicKey = $this->getPublicKeyByRequester($event->getRequest());
        $response = $event->getResponse();
        if (!$response instanceof ResponseInterface) {
            return;
        }

        $event->setResponse($this->sealResponse($response, $publicKey));
    }

    private function sealResponse(ResponseInterface $response, string $publicKey): Response
    {
        $psrResponse = $this->sapient->sealResponse($response, new SealingPublicKey(Base64UrlSafe::decode($publicKey)));

        return $this->httpFoundationFactory->createResponse($psrResponse);
    }

    private function getPublicKeyByRequester(Request $request)
    {
        $publicKey = $this->publicKeyGetter->get($request);
        if ('' === $publicKey) {
            throw new \RuntimeException('Public key not found for requester. Cannot seal response.');
        }

        return $publicKey;
    }
}