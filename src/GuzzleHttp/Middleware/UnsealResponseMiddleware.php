<?php


namespace lepiaf\SapientBundle\GuzzleHttp\Middleware;

use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SealingSecretKey;
use ParagonIE\Sapient\Sapient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UnsealResponseMiddleware
{
    private $sapient;
    private $privateKey;

    public function __construct(Sapient $sapient, string $privateKey)
    {
        $this->sapient = $sapient;
        $this->privateKey = $privateKey;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            return $handler($request, $options)->then(
                function (ResponseInterface $response) {
                    $responseUnsealed = $this->sapient->unsealResponse(
                        $response,
                        new SealingSecretKey(Base64UrlSafe::decode($this->privateKey))
                    );

                    return $responseUnsealed;
                }
            );
        };
    }
}
