<?php

namespace Tests\lepiaf\SapientBundle\EventSubscriber;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use lepiaf\SapientBundle\GuzzleHttp\Middleware\RequesterHeaderMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class RequesterHeaderMiddlewareTest extends TestCase
{
    public function testRequestWithHeader()
    {
        $mockHandler = new MockHandler([
            function (RequestInterface $request) {
                $this->assertTrue($request->hasHeader('Sapient-Requester'));
                $this->assertSame(['client-bob'], $request->getHeader('Sapient-Requester'));

                return new Response(200);
            },
        ]);
        $handler = HandlerStack::create($mockHandler);
        $handler->push(new RequesterHeaderMiddleware('client-bob'));
        $client = new Client(['handler' => $handler]);

        $client->request('GET', 'http://api.example.com/api/ping');
    }
}
