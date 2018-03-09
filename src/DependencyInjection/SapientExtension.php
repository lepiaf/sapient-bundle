<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class SapientExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('console.yml');
        $loader->load('event_subscriber.yml');
        $loader->load('service.yml');

        $container->setParameter('sapient.seal.public', $configs['seal']['public']);
        $container->setParameter('sapient.seal.private', $configs['seal']['private']);
        $container->setParameter('sapient.seal.enabled', $configs['seal']['enabled']);
        $container->setParameter('sapient.sign.public', $configs['sign']['public']);
        $container->setParameter('sapient.sign.private', $configs['sign']['private']);
        $container->setParameter('sapient.sign.enabled', $configs['sign']['enabled']);
        $container->setParameter('sapient.requester_public_keys', $configs['requester_public_keys']);
    }
}
