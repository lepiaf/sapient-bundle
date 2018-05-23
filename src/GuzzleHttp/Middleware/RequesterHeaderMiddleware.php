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
    private $requesterName;

    /**
     * @param string $requesterName
     */
    public function __construct(string $requesterName)
    {
        $this->requesterName = $requesterName;
    }

    public function __invoke(callable $handler): callable
    {
        return function(RequestInterface $request, array $options) use ($handler) {
            return $handler(
                $request->withHeader(PublicKeyGetter::HEADER_REQUESTER, $this->requesterName),
                $options
            );
        };
    }
}
