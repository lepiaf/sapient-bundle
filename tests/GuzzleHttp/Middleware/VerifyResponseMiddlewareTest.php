<?php

namespace Tests\lepiaf\SapientBundle\EventSubscriber;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use lepiaf\SapientBundle\GuzzleHttp\Middleware\VerifyResponseMiddleware;
use lepiaf\SapientBundle\Service\PublicKeyGetter;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SigningSecretKey;
use ParagonIE\Sapient\Sapient;
use PHPUnit\Framework\TestCase;

class VerifyResponseMiddlewareTest extends TestCase
{
    private $privateKey;
    private $publicKey;

    /**
     * @var PublicKeyGetter
     */
    private $publicKeyGetter;

    /**
     * @var Sapient
     */
    private $sapient;

    public function setUp()
    {
        $keyPair = SigningSecretKey::generate();
        $this->privateKey = $keyPair->getString();
        $this->publicKey = $keyPair->getPublickey()->getString();

        $this->sapient = new Sapient();
        $this->publicKeyGetter = new PublicKeyGetter([], [['host' => 'api-alice', 'key' => $this->publicKey]]);
    }

    public function testVerifyResponse()
    {
        $mockHandler = new MockHandler([
            $this->sapient->signResponse(new Response(200, ['Sapient-Signer' => ['api-alice']], 'hello world'), new SigningSecretKey(Base64UrlSafe::decode($this->privateKey)))
        ]);

        $handler = HandlerStack::create($mockHandler);
        $handler->push(new VerifyResponseMiddleware($this->sapient, $this->publicKeyGetter));
        $client = new Client(['handler' => $handler]);

        $response = $client->request('GET', 'http://api.example.com/api/ping');
        $this->assertSame('hello world', (string) $response->getBody());
    }

    /**
     * @expectedExceptionMessage No valid signature given for this HTTP response
     * @expectedException \ParagonIE\Sapient\Exception\InvalidMessageException
     */
    public function testUnsealResponseFailed()
    {
        $otherkeyPair = SigningSecretKey::generate();
        $otherkeyPairPrivate = $otherkeyPair->getString();

        $mockHandler = new MockHandler([
            $this->sapient->signResponse(new Response(200, ['Sapient-Signer' => ['api-alice']], 'hello world'), new SigningSecretKey(Base64UrlSafe::decode($otherkeyPairPrivate)))
        ]);

        $handler = HandlerStack::create($mockHandler);
        $handler->push(new VerifyResponseMiddleware($this->sapient, $this->publicKeyGetter));
        $client = new Client(['handler' => $handler]);

        $response = $client->request('GET', 'http://api.example.com/api/ping');
        $this->assertSame('hello world', (string) $response->getBody());
    }
}
