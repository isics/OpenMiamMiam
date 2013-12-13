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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;


class IsicsOpenMiamMiamExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('open_miam_miam.title', $config['title']);
        $container->setParameter('open_miam_miam.currency', $config['currency']);

        $container->setParameter('open_miam_miam.association.pagination.consumers', $config['association']['pagination']['consumers']);
        $container->setParameter('open_miam_miam.association.pagination.consumer_payments', $config['association']['pagination']['consumer_payments']);
        $container->setParameter('open_miam_miam.super.pagination.user', $config['super']['pagination']['user']);

        $container->setParameter('open_miam_miam.product', $config['product']);
        $container->setParameter('open_miam_miam.consumer', $config['consumer']);
        $container->setParameter('open_miam_miam.order', $config['order']);
        $container->setParameter('open_miam_miam.buying_units', $config['buying_units']);
        $container->setParameter('open_miam_miam.producer', $config['producer']);

        $container->setParameter('open_miam_miam.mailer', $config['mailer']);

        $this->loadValidationFiles($container);
    }

    /**
     * Loads validation files
     *
     * @param ContainerBuilder $container
     */
    private function loadValidationFiles(ContainerBuilder $container)
    {
        $yamlMappingFiles = $container->getParameter('validator.mapping.loader.yaml_files_loader.mapping_files');

        $finder = new Finder();
        foreach ($finder->files()->name('*.yml')->in(__DIR__.'/../Resources/config/validation') as $file) {
            $yamlMappingFiles[] = (string) $file;
        }

        $container->setParameter('validator.mapping.loader.yaml_files_loader.mapping_files', $yamlMappingFiles);
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'isics_open_miam_miam';
    }
}
