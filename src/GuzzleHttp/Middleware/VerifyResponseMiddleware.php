<?php


namespace lepiaf\SapientBundle\GuzzleHttp\Middleware;

use lepiaf\SapientBundle\Service\PublicKeyGetter;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SigningPublicKey;
use ParagonIE\Sapient\Sapient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

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

    /**
     * @var HttpFoundationFactory
     */
    private $httpFoundationFactory;

    public function __construct(Sapient $sapient, PublicKeyGetter $publicKeyGetter, HttpFoundationFactory $httpFoundationFactory)
    {
        $this->sapient = $sapient;
        $this->publicKeyGetter = $publicKeyGetter;
        $this->httpFoundationFactory = $httpFoundationFactory;
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            return $handler($request, $options)->then(
                function (ResponseInterface $response) use ($request) {
                    $httpFoundationRequest = $this->httpFoundationFactory->createRequest($request);
                    $publicKey = $this->publicKeyGetter->get($httpFoundationRequest);
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
