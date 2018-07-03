<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\GuzzleHttp\Middleware;

use lepiaf\SapientBundle\Service\PublicKeyGetter;
use Psr\Http\Message\RequestInterface;

class RequesterHeaderMiddleware
{
    /**
     * @var string
     */
    private $requesterHost;

    public function __construct(string $requesterHost)
    {
        $this->requesterHost = $requesterHost;
    }

    public function __invoke(callable $handler): callable
    {
        return function(RequestInterface $request, array $options) use ($handler) {
            return $handler(
                $request->withHeader(PublicKeyGetter::HEADER_REQUESTER, $this->requesterHost),
                $options
            );
        };
    }
}
