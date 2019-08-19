<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\EventSubscriber;

use lepiaf\SapientBundle\Exception\VerifySignatureException;
use lepiaf\SapientBundle\Service\PublicKeyGetter;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SigningPublicKey;
use ParagonIE\Sapient\Sapient;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class VerifyRequestSubscriber implements EventSubscriberInterface
{
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

    public function __construct(DiactorosFactory $diactorosFactory, Sapient $sapient, PublicKeyGetter $publicKeyGetter)
    {
        $this->diactorosFactory = $diactorosFactory;
        $this->sapient = $sapient;
        $this->publicKeyGetter = $publicKeyGetter;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['verifyRequest', -110],
        ];
    }

    public function verifyRequest(GetResponseEvent $event): void
    {
        $publicKey = $this->getVerifyingKey($event->getRequest());
        $psrRequest = $this->diactorosFactory->createRequest($event->getRequest());
        try {
            $this->sapient->verifySignedRequest(
                $psrRequest,
                new SigningPublicKey(Base64UrlSafe::decode($publicKey))
            );
        } catch (\SodiumException $sodiumException) {
            throw new VerifySignatureException('Cannot verify signature in request.');
        }
    }

    private function getVerifyingKey(Request $request): string
    {
        $psrRequest = $this->diactorosFactory->createRequest($request);

        return $this->publicKeyGetter->getVerifyingKeyFromRequest($psrRequest);
    }
}
