<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class Terminal42OEmbedExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));

        $loader->load('services.yml');

        $container->setParameter('terminal42_oembed.facebook_token', $config['facebook_token']);
    }
}
