<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\EventSubscriber;

use lepiaf\SapientBundle\Exception\VerifyRequestException;
use lepiaf\SapientBundle\Service\PublicKeyGetter;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SigningPublicKey;
use ParagonIE\Sapient\Sapient;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class VerifyRequestSubscriber implements EventSubscriberInterface
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
     * @var PublicKeyGetter
     */
    private $publicKeyGetter;

    public function __construct(DiactorosFactory $diactorosFactory, Sapient $sapient, PublicKeyGetter $publicKeyGetter)
    {
        $this->diactorosFactory = $diactorosFactory;
        $this->sapient = $sapient;
        $this->publicKeyGetter = $publicKeyGetter;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['verifyRequest', 255],
        ];
    }

    public function verifyRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $psrRequest = $this->diactorosFactory->createRequest($request);
        $publicKey = $this->publicKeyGetter->get($request);
        try {
            $this->sapient->verifySignedRequest($psrRequest, new SigningPublicKey(Base64UrlSafe::decode($publicKey)));
        } catch (\Exception $exception) {
            throw new VerifyRequestException('Cannot verify signature in request.', 0, $exception);
        }
    }
}
