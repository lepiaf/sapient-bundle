<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\DependencyInjection;

use lepiaf\SapientBundle\Exception\ConfigurationRequiredException;
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

        $this->loadSignConfiguration($config, $loader, $container);
        $this->loadSealConfiguration($config, $loader, $container);
        $this->loadGuzzleMiddlewareConfiguration($config, $loader, $container);
        $this->loadVerifyRequestConfiguration($config, $loader);
        $this->loadUnsealRequestConfiguration($config, $loader);
    }

    private function loadSignConfiguration(array $config, YamlFileLoader $loader, ContainerBuilder $container): void
    {
        if ($config['sign']['enabled']) {
            $container->setParameter('sapient.sign.public', $config['sign']['public']);
            $container->setParameter('sapient.sign.private', $config['sign']['private']);
            $container->setParameter('sapient.sign.host', $config['sign']['host']);

            if ($config['sign']['response']) {
                $loader->load('sign.yml');
            }
        }
    }

    private function loadSealConfiguration(array $config, YamlFileLoader $loader, ContainerBuilder $container): void
    {
        if ($config['seal']['enabled']) {
            $container->setParameter('sapient.seal.public', $config['seal']['public']);
            $container->setParameter('sapient.seal.private', $config['seal']['private']);

            if (!$config['seal']['response']) {
                return;
            }

            if (!$config['sign']['enabled'] || !$config['sign']['response']) {
                throw new ConfigurationRequiredException('You must enable "sign" option with "sign.response" as true before using "seal.response" feature.');
            }

            $loader->load('seal.yml');
        }
    }

    private function loadGuzzleMiddlewareConfiguration(array $config, YamlFileLoader $loader, ContainerBuilder $container): void
    {
        if ($config['guzzle_middleware']['enabled']) {
            if ($config['guzzle_middleware']['verify']) {
                $loader->load('guzzle_middleware/verify_response.yml');
            }

            if ($config['guzzle_middleware']['unseal']) {
                if (!$config['seal']['enabled']) {
                    throw new ConfigurationRequiredException('You must enable "seal" option and configure a "seal.private" key before using "guzzle_middleware.unseal" feature.');
                }

                $loader->load('guzzle_middleware/unseal_response.yml');
            }

            if ($config['guzzle_middleware']['requester_host']) {
                $container->setParameter('sapient.guzzle_middleware.requester_host', $config['guzzle_middleware']['requester_host']);
                $loader->load('guzzle_middleware/requester_header.yml');
            }

            if ($config['guzzle_middleware']['sign_request']) {
                if (!$config['sign']['enabled']) {
                    throw new ConfigurationRequiredException('You must enable "sign" option and configure a "sign.private" key before using "guzzle_middleware.sign_request" feature.');
                }

                $loader->load('guzzle_middleware/sign_request.yml');
            }

            if ($config['guzzle_middleware']['seal_request']) {
                if (!$config['seal']['enabled']) {
                    throw new ConfigurationRequiredException('You must enable "seal" option and configure a "seal.private" key before using "guzzle_middleware.seal_request" feature.');
                }

                if (!$config['guzzle_middleware']['sign_request']) {
                    throw new ConfigurationRequiredException('You must enable "guzzle_middleware.sign_request" option before using "guzzle_middleware.seal_request" feature.');
                }
                $loader->load('guzzle_middleware/seal_request.yml');
            }
        }
    }

    private function loadUnsealRequestConfiguration(array $config, YamlFileLoader $loader): void
    {
        if ($config['unseal_request']) {
            if (!$config['seal']['enabled']) {
                throw new ConfigurationRequiredException('You must enable "seal" option before using "unseal_request" feature.');
            }

            $loader->load('unseal_request.yml');
        }
    }

    private function loadVerifyRequestConfiguration(array $config, YamlFileLoader $loader): void
    {
        if ($config['verify_request']) {
            $loader->load('verify_request.yml');
        }
    }
}
