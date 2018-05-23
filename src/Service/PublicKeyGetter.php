<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\Service;

use lepiaf\SapientBundle\Exception\{
    RequesterHeaderMissingException,
    NoKeyFoundForRequesterException,
    SignerHeaderMissingException
};
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PublicKeyGetter
{
    public const HEADER_SIGNER = 'Sapient-Signer';
    public const HEADER_REQUESTER = 'Sapient-Requester';

    /**
     * @var array
     */
    private $sealingPublicKeys;

    /**
     * @var array
     */
    private $verifyingPublicKeys;

    public function __construct(array $sealingPublicKeys, array $verifyingPublicKeys)
    {
        $this->sealingPublicKeys = $sealingPublicKeys;
        $this->verifyingPublicKeys = $verifyingPublicKeys;
    }

    /**
     * @param RequestInterface $request
     *
     * @return string
     *
     * @throws RequesterHeaderMissingException
     * @throws NoKeyFoundForRequesterException
     */
    public function getSealingKey(RequestInterface $request): string
    {
        if (!$request->hasHeader(self::HEADER_REQUESTER) || 0 === \count($request->getHeader(self::HEADER_REQUESTER))) {
            throw new RequesterHeaderMissingException(sprintf('%s header is missing.', self::HEADER_REQUESTER));
        }

        foreach ($this->sealingPublicKeys as $sealingPublicKey) {
            if ($request->getHeader(self::HEADER_REQUESTER)[0] === $sealingPublicKey['name']) {
                return $sealingPublicKey['key'];
            }
        }

        throw new NoKeyFoundForRequesterException('Sealing key not found.');
    }

    /**
     * @param ResponseInterface $response
     *
     * @return string
     *
     * @throws SignerHeaderMissingException
     * @throws NoKeyFoundForRequesterException
     */
    public function getVerifyingKey(ResponseInterface $response): string
    {
        if (!$response->hasHeader(self::HEADER_SIGNER) || 0 === \count($response->getHeader(self::HEADER_SIGNER))) {
            throw new SignerHeaderMissingException(sprintf('%s header is missing.', self::HEADER_SIGNER));
        }

        foreach ($this->verifyingPublicKeys as $verifyingPublicKeys) {
            if ($response->getHeader(self::HEADER_SIGNER)[0] === $verifyingPublicKeys['name']) {
                return $verifyingPublicKeys['key'];
            }
        }

        throw new NoKeyFoundForRequesterException('Verifying key not found.');
    }
}
