<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\GuzzleHttp\Middleware;

use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SigningSecretKey;
use ParagonIE\Sapient\Sapient;
use Psr\Http\Message\RequestInterface;

class SignRequestMiddleware
{
    /**
     * @var Sapient
     */
    private $sapient;

    /**
     * @var string
     */
    private $signPrivateKey;

    /**
     * @param Sapient $sapient
     * @param string $signPrivateKey
     */
    public function __construct(Sapient $sapient, string $signPrivateKey)
    {
        $this->sapient = $sapient;
        $this->signPrivateKey = $signPrivateKey;
    }

    public function __invoke(callable $handler): callable
    {
        return function(RequestInterface $request, array $options) use ($handler) {
            return $handler(
                $this->sapient->signRequest($request, new SigningSecretKey(Base64UrlSafe::decode($this->signPrivateKey))),
                $options
            );
        };
    }
}
