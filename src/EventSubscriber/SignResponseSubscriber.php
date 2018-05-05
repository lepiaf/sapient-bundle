<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\EventSubscriber;

use lepiaf\SapientBundle\Service\PublicKeyGetter;
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
    private $signPrivateKey;

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
    private $signName;

    public function __construct(HttpFoundationFactory $httpFoundationFactory, DiactorosFactory $diactorosFactory, Sapient $sapient, string $signPrivateKey, string $signName)
    {
        $this->httpFoundationFactory = $httpFoundationFactory;
        $this->diactorosFactory = $diactorosFactory;
        $this->sapient = $sapient;
        $this->signPrivateKey = $signPrivateKey;
        $this->signName = $signName;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['signPsrResponse', -100],
            KernelEvents::RESPONSE => ['signHttpFoundationResponse', -100],
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
        $psrResponse = $this->sapient->signResponse($response, new SigningSecretKey(Base64UrlSafe::decode($this->signPrivateKey)));
        $psrResponse = $psrResponse->withHeader(PublicKeyGetter::HEADER_SIGNER, $this->signName);

        return $this->httpFoundationFactory->createResponse($psrResponse);
    }
}
