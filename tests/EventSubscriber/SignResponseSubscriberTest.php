<?php

namespace Tests\lepiaf\SapientBundle\EventSubscriber;

use lepiaf\SapientBundle\EventSubscriber\ExceptionSubscriber;
use lepiaf\SapientBundle\EventSubscriber\SealResponseSubscriber;
use lepiaf\SapientBundle\EventSubscriber\SignResponseSubscriber;
use lepiaf\SapientBundle\Service\PublicKeyGetter;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SealingSecretKey;
use ParagonIE\Sapient\CryptographyKeys\SigningPublicKey;
use ParagonIE\Sapient\Sapient;
use ParagonIE\Sapient\Simple;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SignResponseSubscriberTest extends TestCase
{
    const SIGN_KEY_PRIVATE = '-LTOhDx5PzhRoX3Hb26tAfHo_iv5HtVWYigYiYFlwDdIq8dgZ8CpOqH7itvFkmLJeQu8h2SPPOSdvfW8Y-ubUw==';
    const SIGN_KEY_PUBLIC = 'SKvHYGfAqTqh-4rbxZJiyXkLvIdkjzzknb31vGPrm1M=';

    private $httpFoundationFactory;
    private $diactorosFactory;
    /**
     * @var Sapient
     */
    private $sapient;

    public function setUp()
    {
        $this->httpFoundationFactory = new HttpFoundationFactory();
        $this->diactorosFactory = new DiactorosFactory();
        $this->sapient = new Sapient();
    }

    public function testSealHttpResponse()
    {
        $request = Request::create('http://localhost');
        $response = new Response('hello world');
        $event = new FilterResponseEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );

        $subscriber = new SignResponseSubscriber(
            $this->httpFoundationFactory,
            $this->diactorosFactory,
            $this->sapient,
            self::SIGN_KEY_PRIVATE,
            'localhost'
        );
        $subscriber->signHttpFoundationResponse($event);

        $this->assertInstanceOf(
            ResponseInterface::class,
            $this->sapient->verifySignedResponse(
                $this->diactorosFactory->createResponse($event->getResponse()),
                new SigningPublicKey(Base64UrlSafe::decode(self::SIGN_KEY_PUBLIC))
            )
        );
    }

    public function testSignPsrResponse()
    {
        $request = Request::create('http://localhost');
        $response = new \GuzzleHttp\Psr7\Response(200, [], 'hello world');
        $event = new GetResponseForControllerResultEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );

        $subscriber = new SignResponseSubscriber(
            $this->httpFoundationFactory,
            $this->diactorosFactory,
            $this->sapient,
            self::SIGN_KEY_PRIVATE,
            'localhost'
        );
        $subscriber->signPsrResponse($event);

        $this->assertInstanceOf(
            ResponseInterface::class,
            $this->sapient->verifySignedResponse(
                $this->diactorosFactory->createResponse($event->getResponse()),
                new SigningPublicKey(Base64UrlSafe::decode(self::SIGN_KEY_PUBLIC))
            )
        );
    }

    public function testNoResponseForNotPsrResponse()
    {
        $request = Request::create('http://localhost');
        $response = 'hello world';
        $event = new GetResponseForControllerResultEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );

        $subscriber = new SignResponseSubscriber(
            $this->httpFoundationFactory,
            $this->diactorosFactory,
            $this->sapient,
            self::SIGN_KEY_PRIVATE,
            'localhost'
        );
        $subscriber->signPsrResponse($event);
        $this->assertNull($event->getResponse());
    }

    public function testSubscriberRegistration()
    {
        $this->assertSame([
            'kernel.view' => ['signPsrResponse', -100],
            'kernel.response' => ['signHttpFoundationResponse', -100],
        ], SignResponseSubscriber::getSubscribedEvents());
    }
}
