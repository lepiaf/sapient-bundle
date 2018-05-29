<?php

namespace Tests\lepiaf\SapientBundle\EventSubscriber;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use lepiaf\SapientBundle\GuzzleHttp\Middleware\SignRequestMiddleware;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SigningPublicKey;
use ParagonIE\Sapient\CryptographyKeys\SigningSecretKey;
use ParagonIE\Sapient\Sapient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class SignRequestMiddlewareTest extends TestCase
{
    private $privateKey;
    private $publicKey;
    private $sapient;

    public function setUp()
    {
        $this->sapient = new Sapient();
        $keyPair = SigningSecretKey::generate();
        $this->privateKey = $keyPair->getString();
        $this->publicKey = $keyPair->getPublickey()->getString();
    }

    public function testSignRequest()
    {
        $mockHandler = new MockHandler([
            function (RequestInterface $request) {
                $this->assertTrue($request->hasHeader(Sapient::HEADER_SIGNATURE_NAME));
                $this->sapient->verifySignedRequest($request, new SigningPublicKey(Base64UrlSafe::decode($this->publicKey)));

                return new Response(200);
            },
        ]);

        $handler = HandlerStack::create($mockHandler);
        $handler->push(new SignRequestMiddleware($this->sapient, $this->privateKey));
        $client = new Client(['handler' => $handler]);

        $client->request('GET', 'http://api.example.com/api/ping');
    }

    /**
     * @expectedException \ParagonIE\Sapient\Exception\InvalidMessageException
     * @expectedExceptionMessage No valid signature given for this HTTP request
     */
    public function testBadSignRequest()
    {
        $otherSigningKey = SigningSecretKey::generate();
        $otherSigningKeyPrivate = $otherSigningKey->getString();

        $mockHandler = new MockHandler([
            function (RequestInterface $request) {
                $this->assertTrue($request->hasHeader(Sapient::HEADER_SIGNATURE_NAME));
                $this->sapient->verifySignedRequest($request, new SigningPublicKey(Base64UrlSafe::decode($this->publicKey)));

                return new Response(200);
            },
        ]);

        $handler = HandlerStack::create($mockHandler);
        $handler->push(new SignRequestMiddleware($this->sapient, $otherSigningKeyPrivate));
        $client = new Client(['handler' => $handler]);

        $client->request('GET', 'http://api.example.com/api/ping');
    }
}
