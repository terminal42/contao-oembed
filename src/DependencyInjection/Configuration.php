<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('terminal42_oembed');
        $treeBuilder
            ->getRootNode()
            ->children()
            ->scalarNode('facebook_token')
            ->defaultValue('144465555597023|5fe9265371e3f65dbf07738a1a6920a1')
            ->info('Access token for Facebook Graph API')
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
