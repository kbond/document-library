<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zenstruck_document_library');

        $treeBuilder->getRootNode() // @phpstan-ignore-line
            ->children()
                ->arrayNode('libraries')
                    ->info('Library configurations')
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
