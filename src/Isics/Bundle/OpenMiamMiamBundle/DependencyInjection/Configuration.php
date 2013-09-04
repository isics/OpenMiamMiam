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
                ->scalarNode('product_ref_prefix')->defaultValue('PR')->end()
                ->integerNode('product_ref_pad_length')->defaultValue(3)->end()
                ->scalarNode('customer_ref_prefix')->defaultValue('CU')->end()
                ->integerNode('customer_ref_pad_length')->defaultValue(6)->end()
                ->scalarNode('order_ref_prefix')->defaultValue('OR')->end()
                ->integerNode('order_ref_pad_length')->defaultValue(6)->end()
                ->arrayNode('buying_units')
                    ->defaultValue(array('piece', 'g', 'kg', 'm'))
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
