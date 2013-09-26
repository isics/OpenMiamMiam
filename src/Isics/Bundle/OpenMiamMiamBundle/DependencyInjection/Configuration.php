<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('open_miam_miam');

        $rootNode
            ->children()
                ->scalarNode('title')->defaultValue('OpenMiamMiam Demo')->end()
                ->scalarNode('currency')->defaultValue('EUR')->end()
                ->arrayNode('producer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('nb_next_producer_attendances_to_define')->defaultValue(5)->end()
                        ->scalarNode('upload_path')->defaultValue('/uploads/producer')->end()
                    ->end()
                ->end()
                ->arrayNode('product')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('ref_prefix')->defaultValue('PR')->end()
                        ->integerNode('ref_pad_length')->defaultValue(3)->end()
                        ->scalarNode('upload_path')->defaultValue('/uploads/products')->end()
                    ->end()
                ->end()
                ->arrayNode('consumer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('ref_prefix')->defaultValue('CU')->end()
                        ->integerNode('ref_pad_length')->defaultValue(6)->end()
                    ->end()
                ->end()
                ->arrayNode('order')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('ref_prefix')->defaultValue('OR')->end()
                        ->integerNode('ref_pad_length')->defaultValue(6)->end()
                    ->end()
                ->end()
                ->arrayNode('buying_units')
                    ->defaultValue(array('piece', 'g', 'kg', 'm'))
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
