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
                ->arrayNode('association')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('pagination')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('consumers')->defaultValue(50)->end()
                                ->scalarNode('consumer_payments')->defaultValue(50)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('producer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('nb_next_producer_attendances_to_define')->defaultValue(5)->end()
                        ->scalarNode('upload_path')->defaultValue('/uploads/producers')->end()
                    ->end()
                ->end()
                ->arrayNode('product')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('artificial_product_name')->defaultValue('Artificial product')->end()
                        ->scalarNode('artificial_product_ref')->defaultValue('MISC')->end()
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
                        ->scalarNode('artificial_product_ref')->defaultValue('MISC')->end()
                        ->scalarNode('ref_prefix')->defaultValue('OR')->end()
                        ->integerNode('ref_pad_length')->defaultValue(6)->end()
                    ->end()
                ->end()
                ->arrayNode('buying_units')
                    ->defaultValue(array('piece', 'g', 'kg', 'm'))
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('mailer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('sender_address')->defaultValue('%mailer_sender_address%')->end()
                        ->scalarNode('sender_name')->defaultValue('%mailer_sender_name%')->end()
                    ->end()
                ->end()
                ->scalarNode('sales_orders_pdf_view')->defaultValue('IsicsOpenMiamMiamBundle:Pdf:salesOrder.html.twig')->end()
                ->scalarNode('producer_sales_orders_pdf_view')->defaultValue('IsicsOpenMiamMiamBundle:Pdf:producerSalesOrder.html.twig')->end()
                ->scalarNode('products_to_prepare_pdf_view')->defaultValue('IsicsOpenMiamMiamBundle:Pdf:productsToPrepare.html.twig')->end()
            ->end();

        return $treeBuilder;
    }
}
