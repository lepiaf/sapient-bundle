<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\GuzzleHttp\Middleware;

use lepiaf\SapientBundle\Service\PublicKeyGetter;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SigningPublicKey;
use ParagonIE\Sapient\Sapient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class VerifyResponseMiddleware
{
    /**
     * @var Sapient
     */
    private $sapient;

    /**
     * @var PublicKeyGetter
     */
    private $publicKeyGetter;

    public function __construct(Sapient $sapient, PublicKeyGetter $publicKeyGetter)
    {
        $this->sapient = $sapient;
        $this->publicKeyGetter = $publicKeyGetter;
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            return $handler($request, $options)->then(
                function (ResponseInterface $response) {
                    $publicKey = $this->publicKeyGetter->getVerifyingKey($response);
                    $this->sapient->verifySignedResponse(
                        $response,
                        new SigningPublicKey(Base64UrlSafe::decode($publicKey))
                    );

                    return $response;
                }
            );
        };
    }
}
