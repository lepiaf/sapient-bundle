<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\Service;

use lepiaf\SapientBundle\EventSubscriber\NoKeyFoundForRequesterException;
use lepiaf\SapientBundle\EventSubscriber\OriginHeaderMissingException;
use Symfony\Component\HttpFoundation\Request;

class PublicKeyGetter
{
    /**
     * @var array
     */
    private $requesterPublicKeys;

    public function __construct(array $requesterPublicKeys)
    {
        $this->requesterPublicKeys = $requesterPublicKeys;
    }

    /**
     * @param Request $request
     *
     * @return string
     *
     * @throws OriginHeaderMissingException
     * @throws NoKeyFoundForRequesterException
     */
    public function get(Request $request): string
    {
        if (!$request->headers->has('X-Origin')) {
            throw new OriginHeaderMissingException('X-Origin header is missing.');
        }

        foreach ($this->requesterPublicKeys as $requesterPublicKey) {
            if ($request->headers->get('X-Origin') === $requesterPublicKey['origin']) {
                return $requesterPublicKey['key'];
            }
        }

        throw new NoKeyFoundForRequesterException('Public key not found for requester.');
    }
}
