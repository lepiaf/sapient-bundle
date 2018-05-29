<?php

namespace Tests\lepiaf\SapientBundle\EventSubscriber;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use lepiaf\SapientBundle\GuzzleHttp\Middleware\UnsealResponseMiddleware;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SealingPublicKey;
use ParagonIE\Sapient\CryptographyKeys\SealingSecretKey;
use ParagonIE\Sapient\Sapient;
use PHPUnit\Framework\TestCase;

class UnsealResponseMiddlewareTest extends TestCase
{
    private $privateKey;
    private $publicKey;
    /**
     * @var Sapient
     */
    private $sapient;

    public function setUp()
    {
        $this->sapient = new Sapient();
        $keyPair = SealingSecretKey::generate();
        $this->privateKey = $keyPair->getString();
        $this->publicKey = $keyPair->getPublickey()->getString();
    }

    public function testUnsealResponse()
    {
        $mockHandler = new MockHandler([
            $this->sapient->sealResponse(new Response(200, [], 'hello world'), new SealingPublicKey(Base64UrlSafe::decode($this->publicKey)))
        ]);

        $handler = HandlerStack::create($mockHandler);
        $handler->push(new UnsealResponseMiddleware($this->sapient, $this->privateKey));
        $client = new Client(['handler' => $handler]);

        $response = $client->request('GET', 'http://api.example.com/api/ping');
        $this->assertSame('hello world', (string) $response->getBody());
    }

    /**
     * @expectedExceptionMessage Invalid MAC
     * @expectedException \SodiumException
     */
    public function testUnsealResponseFailed()
    {
        $otherSealingKey = SealingSecretKey::generate();
        $otherSealingKeyPublic = $otherSealingKey->getPublickey()->getString();
        
        $mockHandler = new MockHandler([
            $this->sapient->sealResponse(new Response(200, [], 'hello world'), new SealingPublicKey(Base64UrlSafe::decode($otherSealingKeyPublic)))
        ]);

        $handler = HandlerStack::create($mockHandler);
        $handler->push(new UnsealResponseMiddleware($this->sapient, $this->privateKey));
        $client = new Client(['handler' => $handler]);

        $response = $client->request('GET', 'http://api.example.com/api/ping');
        $this->assertSame('hello world', (string) $response->getBody());
    }
}
