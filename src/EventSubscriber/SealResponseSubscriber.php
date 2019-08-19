<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\EventSubscriber;

use lepiaf\SapientBundle\Exception\SealResponseException;
use lepiaf\SapientBundle\Exception\WrongKeyException;
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
        $publicKey = $this->getSealingKey($event->getRequest());
        $psrResponse = $this->diactorosFactory->createResponse($event->getResponse());
        $event->setResponse($this->sealResponse($psrResponse, $publicKey));
    }

    public function sealPsrResponse(GetResponseForControllerResultEvent $event): void
    {
        $response = $event->getControllerResult();
        if (!$response instanceof ResponseInterface) {
            return;
        }

        $publicKey = $this->getSealingKey($event->getRequest());
        $event->setResponse($this->sealResponse($response, $publicKey));
    }

    private function sealResponse(ResponseInterface $response, string $publicKey): Response
    {
        try {
            $psrResponse = $this->sapient->sealResponse($response, new SealingPublicKey(Base64UrlSafe::decode($publicKey)));
        } catch (\RangeException $rangeException) {
            throw new WrongKeyException($rangeException->getMessage());
        } catch (\SodiumException $sodiumException) {
            throw new SealResponseException('Cannot seal response.');
        }

        return $this->httpFoundationFactory->createResponse($psrResponse);
    }

    private function getSealingKey(Request $request)
    {
        $psrRequest = $this->diactorosFactory->createRequest($request);

        return $this->publicKeyGetter->getSealingKey($psrRequest);
    }
}
