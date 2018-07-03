<?php

namespace Tests\lepiaf\SapientBundle\EventSubscriber;

use GuzzleHttp\Psr7\ServerRequest;
use lepiaf\SapientBundle\EventSubscriber\VerifyRequestSubscriber;
use lepiaf\SapientBundle\Service\PublicKeyGetter;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SigningSecretKey;
use ParagonIE\Sapient\Sapient;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class VerifyRequestSubscriberTest extends TestCase
{
    /**
     * @var HttpFoundationFactory
     */
    private $httpFoundationFactory;
    private $diactorosFactory;
    /**
     * @var Sapient
     */
    private $sapient;
    private $privateKey;
    private $publicKey;

    /**
     * @var PublicKeyGetter
     */
    private $publicKeyGetter;

    public function setUp()
    {
        $keyPair = SigningSecretKey::generate();
        $this->publicKey = $keyPair->getPublickey()->getString();
        $this->privateKey = $keyPair->getString();

        $this->httpFoundationFactory = new HttpFoundationFactory();
        $this->diactorosFactory = new DiactorosFactory();
        $this->sapient = new Sapient();
        $this->publicKeyGetter = new PublicKeyGetter([], [['host' => 'client-bob', 'key' => $this->publicKey]]);
    }

    public function testVerifyRequest()
    {
        $psrRequest = new ServerRequest('GET', 'http://api.example.com/api/ping', ['Sapient-Requester' => ['client-bob']]);
        $psrRequestSigned = $this->sapient->signRequest($psrRequest, new SigningSecretKey(Base64UrlSafe::decode($this->privateKey)));

        $request = $this->httpFoundationFactory->createRequest($psrRequestSigned);
        $event = new GetResponseEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $subscriber = new VerifyRequestSubscriber(
            $this->diactorosFactory,
            $this->sapient,
            $this->publicKeyGetter
        );
        $subscriber->verifyRequest($event);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Request::class, $event->getRequest());
    }

    /**
     * @expectedException \ParagonIE\Sapient\Exception\InvalidMessageException
     * @expectedExceptionMessage No valid signature given for this HTTP request
     */
    public function testVerifyRequestFail()
    {
        $otherSigningKey = SigningSecretKey::generate();
        $otherSigningKeyPrivate = $otherSigningKey->getString();

        $psrRequest = new ServerRequest('GET', 'http://api.example.com/api/ping', ['Sapient-Requester' => ['client-bob']]);
        $psrRequestSigned = $this->sapient->signRequest($psrRequest, new SigningSecretKey(Base64UrlSafe::decode($otherSigningKeyPrivate)));

        $request = $this->httpFoundationFactory->createRequest($psrRequestSigned);
        $event = new GetResponseEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $subscriber = new VerifyRequestSubscriber(
            $this->diactorosFactory,
            $this->sapient,
            $this->publicKeyGetter
        );
        $subscriber->verifyRequest($event);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Request::class, $event->getRequest());
    }

    public function testSubscriberRegistration()
    {
        $this->assertSame([
            'kernel.request' => 'verifyRequest',
        ], VerifyRequestSubscriber::getSubscribedEvents());
    }
}
