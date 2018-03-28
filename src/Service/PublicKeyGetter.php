<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\Service;

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

    public function get(Request $request): string
    {
        if (!$request->headers->has('X-Origin')) {
            return '';
        }

        return false !== isset($this->requesterPublicKeys[$request->headers->get('X-Origin')]) ?: '';
    }
}
