<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Menu;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function adminMenu(FactoryInterface $factory, array $options)
    {
        $object = $options['object'];

        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav nav-pills nav-stacked well');

        $translator = $this->container->get('translator');

        $labelFormat = '<span class="glyphicon glyphicon-%s"></span> %s';

        /* if (current user has role admin) {
            // @todo
        } else */if ($object instanceof Association) {
            // @todo

        } else if ($object instanceof Producer) {
            $menu->addChild('Dashboard', array(
                'route'           => 'open_miam_miam.admin.producer.dashboard',
                'routeParameters' => array('id' => $object->getId()),
                'label'           => sprintf($labelFormat, 'home', $translator->trans('dashboard')),
                'extras'          => array('safe_label' => true),
            ));
            $menu->addChild('Orders', array(
                'uri'             => '#',
                // 'route'           => 'open_miam_miam.admin.producer.list_products',
                // 'routeParameters' => array('id' => $object->getId()),
                'label'           => sprintf($labelFormat, 'shopping-cart', $translator->trans('orders')),
                'extras'          => array('safe_label' => true),
            ));
            $menu->addChild('Products', array(
                'route'           => 'open_miam_miam.admin.producer.list_products',
                'routeParameters' => array('id' => $object->getId()),
                'label'           => sprintf($labelFormat, 'list', $translator->trans('products')),
                'extras'          => array('safe_label' => true),
            ));
            $menu->addChild('Calendar', array(
                'uri'             => '#',
                // 'route'           => 'open_miam_miam.admin.producer.list_products',
                // 'routeParameters' => array('id' => $object->getId()),
                'label'           => sprintf($labelFormat, 'time', $translator->trans('calendar')),
                'extras'          => array('safe_label' => true),
            ));
            $menu->addChild('Managers', array(
                'uri'             => '#',
                // 'route'           => 'open_miam_miam.admin.producer.list_products',
                // 'routeParameters' => array('id' => $object->getId()),
                'label'           => sprintf($labelFormat, 'lock', $translator->trans('managers')),
                'extras'          => array('safe_label' => true),
            ));
            $menu->addChild('Producer infos', array(
                'uri'             => '#',
                // 'route'           => 'open_miam_miam.admin.producer.list_products',
                // 'routeParameters' => array('id' => $object->getId()),
                'label'           => sprintf($labelFormat, 'user', $translator->trans('producer.infos')),
                'extras'          => array('safe_label' => true),
            ));
        }/* else if ($object instanceof Relay) {
            // @todo
        }*/

        return $menu;
    }
}