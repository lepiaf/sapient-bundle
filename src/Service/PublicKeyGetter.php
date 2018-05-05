<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\Service;

use lepiaf\SapientBundle\Exception\{
    OriginHeaderMissingException,
    NoKeyFoundForRequesterException
};
use Symfony\Component\HttpFoundation\Request;

class PublicKeyGetter
{
    /**
     * @var array
     */
    private $clientPublicKeys;

    /**
     * @var array
     */
    private $serverPublicKeys;

    public function __construct(array $clientPublicKeys, array $serverPublicKeys)
    {
        $this->clientPublicKeys = $clientPublicKeys;
        $this->serverPublicKeys = $serverPublicKeys;
    }

    /**
     * @param Request $request
     *
     * @return string
     *
     * @throws OriginHeaderMissingException
     * @throws NoKeyFoundForRequesterException
     */
    public function getClientKey(Request $request): string
    {
        if (!$request->headers->has('X-Origin')) {
            throw new OriginHeaderMissingException('X-Origin header is missing.');
        }

        foreach ($this->clientPublicKeys as $clientPublicKey) {
            if ($request->headers->get('X-Origin') === $clientPublicKey['name']) {
                return $clientPublicKey['key'];
            }
        }

        throw new NoKeyFoundForRequesterException('Public key not found for requester.');
    }

    /**
     * @param Request $request
     *
     * @return string
     *
     * @throws OriginHeaderMissingException
     * @throws NoKeyFoundForRequesterException
     */
    public function getServerKey(Request $request): string
    {
        if (!$request->headers->has('X-Origin')) {
            throw new OriginHeaderMissingException('X-Origin header is missing.');
        }

        foreach ($this->serverPublicKeys as $serverPublicKey) {
            if ($request->headers->get('X-Origin') === $serverPublicKey['name']) {
                return $serverPublicKey['key'];
            }
        }

        throw new NoKeyFoundForRequesterException('Public key not found for requester.');
    }
}
