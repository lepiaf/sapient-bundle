<?php

namespace Tests\lepiaf\SapientBundle\Service;

use lepiaf\SapientBundle\Service\PublicKeyGetter;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PublicKeyGetterTest extends TestCase
{
    public function testGetSealingKey()
    {
        $publicKeyGetter = new PublicKeyGetter(
            [
                ['name' => 'foo', 'key' => 'bar']
            ],
            []
        );

        $request = $this->prophesize(RequestInterface::class);
        $request->hasHeader('Sapient-Requester')->shouldBeCalled()->willReturn(true);
        $request->getHeader('Sapient-Requester')->shouldBeCalled()->willReturn(['foo']);
        $this->assertSame('bar', $publicKeyGetter->getSealingKey($request->reveal()));
    }

    /**
     * @expectedException \lepiaf\SapientBundle\Exception\RequesterHeaderMissingException
     */
    public function testRequesterHeaderMissing()
    {
        $publicKeyGetter = new PublicKeyGetter(
            [
                ['name' => 'foo', 'key' => 'bar']
            ],
            []
        );

        $request = $this->prophesize(RequestInterface::class);
        $request->hasHeader('Sapient-Requester')->shouldBeCalled()->willReturn(false);
        $this->assertSame('bar', $publicKeyGetter->getSealingKey($request->reveal()));
    }

    /**
     * @expectedException \lepiaf\SapientBundle\Exception\RequesterHeaderMissingException
     */
    public function testHeaderEmpty()
    {
        $publicKeyGetter = new PublicKeyGetter(
            [
                ['name' => 'foo', 'key' => 'bar']
            ],
            []
        );

        $request = $this->prophesize(RequestInterface::class);
        $request->hasHeader('Sapient-Requester')->shouldBeCalled()->willReturn(true);
        $request->getHeader('Sapient-Requester')->shouldBeCalled()->willReturn([]);
        $this->assertSame('bar', $publicKeyGetter->getSealingKey($request->reveal()));
    }

    /**
     * @expectedException \lepiaf\SapientBundle\Exception\NoKeyFoundForRequesterException
     */
    public function testKeyNotFound()
    {
        $publicKeyGetter = new PublicKeyGetter(
            [
                ['name' => 'foo', 'key' => 'bar']
            ],
            []
        );

        $request = $this->prophesize(RequestInterface::class);
        $request->hasHeader('Sapient-Requester')->shouldBeCalled()->willReturn(true);
        $request->getHeader('Sapient-Requester')->shouldBeCalled()->willReturn(['baz']);
        $this->assertSame('bar', $publicKeyGetter->getSealingKey($request->reveal()));
    }

    public function testGetVerifyingKey()
    {
        $publicKeyGetter = new PublicKeyGetter(
            [],
            [
                ['name' => 'foo', 'key' => 'bar']
            ]
        );

        $response = $this->prophesize(ResponseInterface::class);
        $response->hasHeader('Sapient-Signer')->shouldBeCalled()->willReturn(true);
        $response->getHeader('Sapient-Signer')->shouldBeCalled()->willReturn(['foo']);
        $this->assertSame('bar', $publicKeyGetter->getVerifyingKey($response->reveal()));
    }

    /**
     * @expectedException \lepiaf\SapientBundle\Exception\SignerHeaderMissingException
     */
    public function testSignerHeaderMissing()
    {
        $publicKeyGetter = new PublicKeyGetter(
            [],
            [
                ['name' => 'foo', 'key' => 'bar']
            ]
        );

        $response = $this->prophesize(ResponseInterface::class);
        $response->hasHeader('Sapient-Signer')->shouldBeCalled()->willReturn(false);
        $this->assertSame('bar', $publicKeyGetter->getVerifyingKey($response->reveal()));
    }

    /**
     * @expectedException \lepiaf\SapientBundle\Exception\SignerHeaderMissingException
     */
    public function testHeaderEmptyForSigner()
    {
        $publicKeyGetter = new PublicKeyGetter(
            [],
            [
                ['name' => 'foo', 'key' => 'bar']
            ]
        );

        $response = $this->prophesize(ResponseInterface::class);
        $response->hasHeader('Sapient-Signer')->shouldBeCalled()->willReturn(true);
        $response->getHeader('Sapient-Signer')->shouldBeCalled()->willReturn([]);
        $this->assertSame('bar', $publicKeyGetter->getVerifyingKey($response->reveal()));
    }

    /**
     * @expectedException \lepiaf\SapientBundle\Exception\NoKeyFoundForRequesterException
     */
    public function testKeyNotFoundForSigner()
    {
        $publicKeyGetter = new PublicKeyGetter(
            [],
            [
                ['name' => 'foo', 'key' => 'bar']
            ]
        );

        $response = $this->prophesize(ResponseInterface::class);
        $response->hasHeader('Sapient-Signer')->shouldBeCalled()->willReturn(true);
        $response->getHeader('Sapient-Signer')->shouldBeCalled()->willReturn(['baz']);
        $this->assertSame('bar', $publicKeyGetter->getVerifyingKey($response->reveal()));
    }
}
