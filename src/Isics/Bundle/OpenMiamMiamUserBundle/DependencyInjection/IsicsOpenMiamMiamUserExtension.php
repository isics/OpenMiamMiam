<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamUserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class IsicsOpenMiamMiamUserExtension extends Extension
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

        if (0 >= (int)$config['last_order_nb_days_considering_customer']) {
            throw new \InvalidArgumentException('Argument open_miam_miam_user.last_order_nb_days_considering_customer must be greater than 0');
        }

        $container->setParameter('open_miam_miam_user.last_order_nb_days_considering_customer', $config['last_order_nb_days_considering_customer']);
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'isics_open_miam_miam_user';
    }
}
