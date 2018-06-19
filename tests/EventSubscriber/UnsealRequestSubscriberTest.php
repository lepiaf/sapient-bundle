<?php

namespace Tests\lepiaf\SapientBundle\EventSubscriber;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\ServerRequest;
use lepiaf\SapientBundle\EventSubscriber\UnsealRequestSubscriber;
use lepiaf\SapientBundle\EventSubscriber\VerifyRequestSubscriber;
use lepiaf\SapientBundle\Service\PublicKeyGetter;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SealingPublicKey;
use ParagonIE\Sapient\CryptographyKeys\SealingSecretKey;
use ParagonIE\Sapient\CryptographyKeys\SigningPublicKey;
use ParagonIE\Sapient\CryptographyKeys\SigningSecretKey;
use ParagonIE\Sapient\Sapient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class UnsealRequestSubscriberTest extends TestCase
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
        $keyPair = SealingSecretKey::generate();
        $this->publicKey = $keyPair->getPublickey()->getString();
        $this->privateKey = $keyPair->getString();

        $this->httpFoundationFactory = new HttpFoundationFactory();
        $this->diactorosFactory = new DiactorosFactory();
        $this->sapient = new Sapient();
        $this->publicKeyGetter = new PublicKeyGetter([], [['name' => 'client-bob', 'key' => $this->publicKey]]);
    }

    public function testUnsealRequest()
    {
        $psrRequest = new ServerRequest(
            'POST',
            'http://api.example.com/api/ping',
            ['Sapient-Requester' => ['client-bob']],
            'hello world'
        );
        $psrRequestSigned = $this->sapient->sealRequest($psrRequest, new SealingPublicKey(Base64UrlSafe::decode($this->publicKey)));

        $request = $this->httpFoundationFactory->createRequest($psrRequestSigned);
        $event = new GetResponseEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $subscriber = new UnsealRequestSubscriber(
            $this->sapient,
            $this->diactorosFactory,
            $this->privateKey
        );
        $subscriber->unsealRequest($event);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Request::class, $event->getRequest());
        $this->assertSame('hello world', $event->getRequest()->getContent());
    }

    /**
     * @expectedException \SodiumException
     * @expectedExceptionMessage Invalid MAC
     */
    public function testUnsealRequestFail()
    {
        $otherSealingKey = SealingSecretKey::generate();
        $otherSealingKeyPrivate = $otherSealingKey->getString();

        $psrRequest = new ServerRequest('GET', 'http://api.example.com/api/ping', ['Sapient-Requester' => ['client-bob']]);
        $psrRequestSigned = $this->sapient->sealRequest($psrRequest, new SealingPublicKey(Base64UrlSafe::decode($otherSealingKeyPrivate)));

        $request = $this->httpFoundationFactory->createRequest($psrRequestSigned);
        $event = new GetResponseEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $subscriber = new UnsealRequestSubscriber(
            $this->sapient,
            $this->diactorosFactory,
            $this->privateKey
        );
        $subscriber->unsealRequest($event);
    }

    public function testSubscriberRegistration()
    {
        $this->assertSame([
            'kernel.request' => ['unsealRequest', -100],
        ], UnsealRequestSubscriber::getSubscribedEvents());
    }
}
