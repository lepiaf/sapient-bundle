<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\GuzzleHttp\Middleware;

use lepiaf\SapientBundle\Service\PublicKeyGetter;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SealingPublicKey;
use ParagonIE\Sapient\Sapient;
use Psr\Http\Message\RequestInterface;

class SealRequestMiddleware
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
        return function(RequestInterface $request, array $options) use ($handler) {
            $publicKey = $this->publicKeyGetter->getSealingKey($request);

            return $handler(
                $this->sapient->sealRequest($request, new SealingPublicKey(Base64UrlSafe::decode($publicKey))),
                $options
            );
        };
    }
}
