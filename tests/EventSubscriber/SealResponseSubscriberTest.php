<?php

namespace Tests\lepiaf\SapientBundle\EventSubscriber;

use lepiaf\SapientBundle\EventSubscriber\ExceptionSubscriber;
use lepiaf\SapientBundle\EventSubscriber\SealResponseSubscriber;
use lepiaf\SapientBundle\Service\PublicKeyGetter;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SealingSecretKey;
use ParagonIE\Sapient\Sapient;
use ParagonIE\Sapient\Simple;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SealResponseSubscriberTest extends TestCase
{
    const SEALING_KEY_PRIVATE = 'FzyiZAbEuquHUXt-YNF6WOXFB6CVBpyz2ocMMaT0FK8=';
    const SEALING_KEY_PUBLIC = 'M2SMMPHg9NOXoX3NgzlWY8iTheyu8qSovnTZpAlIGB0=';

    private $httpFoundationFactory;
    private $diactorosFactory;
    private $sapient;
    private $publicKeyGetter;

    public function setUp()
    {
        $this->httpFoundationFactory = new HttpFoundationFactory();
        $this->diactorosFactory = new DiactorosFactory();
        $this->sapient = new Sapient();
        $this->publicKeyGetter = $this->prophesize(PublicKeyGetter::class);
    }

    public function testSealHttpResponse()
    {
        $request = Request::create('http://localhost');
        $request->headers->add(['Sapient-Requester' => 'localhost']);
        $response = new Response('hello world');
        $event = new FilterResponseEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );
        $this->publicKeyGetter->getSealingKey(Argument::type(RequestInterface::class))->willReturn(self::SEALING_KEY_PUBLIC);

        $subscriber = new SealResponseSubscriber(
            $this->httpFoundationFactory,
            $this->diactorosFactory,
            $this->sapient,
            $this->publicKeyGetter->reveal()
        );
        $subscriber->sealHttpFoundationResponse($event);

        $content = $event->getResponse()->getContent();
        $unsealed = Simple::unseal(
            Base64UrlSafe::decode($content),
            new SealingSecretKey(Base64UrlSafe::decode(self::SEALING_KEY_PRIVATE))
        );
        $this->assertSame('hello world', $unsealed);
    }

    public function testSealPsrResponse()
    {
        $request = Request::create('http://localhost');
        $request->headers->add(['Sapient-Requester' => 'localhost']);
        $response = new \GuzzleHttp\Psr7\Response(200, [], 'hello world');
        $event = new GetResponseForControllerResultEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );
        $this->publicKeyGetter->getSealingKey(Argument::type(RequestInterface::class))->willReturn(self::SEALING_KEY_PUBLIC);

        $subscriber = new SealResponseSubscriber(
            $this->httpFoundationFactory,
            $this->diactorosFactory,
            $this->sapient,
            $this->publicKeyGetter->reveal()
        );
        $subscriber->sealPsrResponse($event);

        $content = $event->getResponse()->getContent();
        $unsealed = Simple::unseal(
            Base64UrlSafe::decode($content),
            new SealingSecretKey(Base64UrlSafe::decode(self::SEALING_KEY_PRIVATE))
        );
        $this->assertSame('hello world', $unsealed);
    }

    public function testNoResponseForNotPsrResponse()
    {
        $request = Request::create('http://localhost');
        $request->headers->add(['Sapient-Requester' => 'localhost']);
        $response = 'some string';
        $event = new GetResponseForControllerResultEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );
        $this->publicKeyGetter->getSealingKey(Argument::type(RequestInterface::class))->willReturn(self::SEALING_KEY_PUBLIC);

        $subscriber = new SealResponseSubscriber(
            $this->httpFoundationFactory,
            $this->diactorosFactory,
            $this->sapient,
            $this->publicKeyGetter->reveal()
        );
        $subscriber->sealPsrResponse($event);

        $this->assertNull($event->getResponse());
    }

    public function testSubscriberRegistration()
    {
        $this->assertSame([
            'kernel.view' => ['sealPsrResponse', -110],
            'kernel.response' => ['sealHttpFoundationResponse', -110],
        ], SealResponseSubscriber::getSubscribedEvents());
    }
}
