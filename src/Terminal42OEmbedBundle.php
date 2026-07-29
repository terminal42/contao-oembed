<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class Terminal42OEmbedBundle extends AbstractBundle
{
    protected string $extensionAlias = 'terminal42_oembed';

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $container): void
    {
        $configurator
            ->services()
            ->load(__NAMESPACE__.'\\', '../src/')
            ->autoconfigure()
            ->autowire()
        ;

        $configurator->parameters()->set('terminal42_oembed.facebook_token', $config['facebook_token'] ?? null);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $rootNode = $definition->rootNode();
        $rootNode
            ->children()
                ->scalarNode('facebook_token')
                    ->info('Access token for Facebook Graph API')
                ->end()
            ->end()
        ;
    }
}
