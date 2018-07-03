<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class SapientExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('console.yml');
        $loader->load('service.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('sapient.sealing_public_keys', $config['sealing_public_keys']);
        $container->setParameter('sapient.verifying_public_keys', $config['verifying_public_keys']);

        if ($config['seal']['enabled']) {
            $loader->load('seal.yml');

            $container->setParameter('sapient.seal.public', $config['seal']['public']);
            $container->setParameter('sapient.seal.private', $config['seal']['private']);
        }

        if ($config['sign']['enabled']) {
            $loader->load('sign.yml');

            $container->setParameter('sapient.sign.public', $config['sign']['public']);
            $container->setParameter('sapient.sign.private', $config['sign']['private']);
            $container->setParameter('sapient.sign.host', $config['sign']['host']);
        }

        if ($config['verify_request']) {
            $loader->load('verify_request.yml');

        }

        if ($config['guzzle_middleware']['enabled']) {
            if ($config['guzzle_middleware']['verify']) {
                $loader->load('guzzle_middleware/verify_response.yml');
            }

            if ($config['guzzle_middleware']['unseal']) {
                $loader->load('guzzle_middleware/unseal_response.yml');
            }

            if ($config['guzzle_middleware']['requester_host']) {
                $container->setParameter('sapient.guzzle_middleware.requester_host', $config['guzzle_middleware']['requester_host']);
                $loader->load('guzzle_middleware/requester_header.yml');
            }

            if ($config['guzzle_middleware']['sign_request']) {
                if (!$config['sign']['enabled']) {
                    throw new \LogicException('You must enable "sign" option before using "sign_request" feature.');
                }

                $loader->load('guzzle_middleware/sign_request.yml');
            }
        }
    }
}
