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

        if ($config['seal']['enabled']) {
            $loader->load('seal.yml');

            $container->setParameter('sapient.seal.public', $config['seal']['public']);
            $container->setParameter('sapient.seal.private', $config['seal']['private']);
            $container->setParameter('sapient.requester_public_keys', $config['requester_public_keys']);
        }

        if ($config['sign']['enabled']) {
            $loader->load('sign.yml');

            $container->setParameter('sapient.sign.public', $config['sign']['public']);
            $container->setParameter('sapient.sign.private', $config['sign']['private']);
        }

//        if ($config['must_verify_signed_request']) {
//            $loader->load('verify_signed_request.yml');
//            $container->setParameter('sapient.requester_public_keys', $config['requester_public_keys']);
//        }

        if ($config['guzzle_middleware']['enabled']) {
            $container->setParameter('sapient.requester_public_keys', $config['requester_public_keys']);

            if ($config['guzzle_middleware']['verify']) {
                $loader->load('guzzle_middleware/verify_response.yml');
            }

            if ($config['guzzle_middleware']['unseal']) {
                $loader->load('guzzle_middleware/unseal_response.yml');
            }
        }
    }
}
