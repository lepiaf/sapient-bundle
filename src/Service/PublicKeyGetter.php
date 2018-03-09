<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\EventSubscriber;

use Symfony\Component\HttpFoundation\Request;

class PublicKeyGetter
{
    /**
     * @var array
     */
    private $requesterPublicKeys;

    /**
     * @var bool
     */
    private $strict;

    public function __construct(array $requesterPublicKeys, bool $strict = true)
    {
    }

    public function get(Request $request): string
    {
        if (!$request->headers->has('X-Origin')) {
            return '';
        }

        return false !== isset($this->requesterPublicKeys[$request->headers->get('X-Origin')]) ?: '';
    }
}
