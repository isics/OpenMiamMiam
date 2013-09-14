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
use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\AdminResource;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    protected $labelFormat = '<span class="glyphicon glyphicon-%s"></span> %s';

    public function adminMenu(FactoryInterface $factory, array $options)
    {
        $adminManager = $this->container->get('open_miam_miam.admin_manager');

        $menu = $factory->createItem('root');

        foreach ($adminManager->findAvailableAdminResources() as $resource) {
            switch ($resource->getType()) {
                case AdminResource::TYPE_SUPER_ADMIN:
                    $this->addSuperAdminMenu($menu);
                    break;
                case AdminResource::TYPE_ASSOCIATION:
                    $this->addAssociationMenu($menu, $resource);
                    break;
                case AdminResource::TYPE_PRODUCER:
                    $this->addProducerMenu($menu, $resource);
                    break;
                case AdminResource::TYPE_RELAY:
                    $this->addRelayMenu($menu, $resource);
            }
        }

        return $menu;
    }

    protected function addSuperAdminMenu(ItemInterface $menu)
    {
    }

    protected function addProducerMenu(ItemInterface $menu, $resource)
    {
        $producer   = $resource->getEntity();
        $translator = $this->container->get('translator');

        $menuName = sprintf('producer%s', $producer->getId());

        $menu->addChild($menuName, array(
            'route'           => 'open_miam_miam.admin.producer.dashboard',
            'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf('%s (%s)', $producer->getName(), $translator->trans('producer')),
        ));
        $menu[$menuName]->addChild('Dashboard', array(
            'route'           => 'open_miam_miam.admin.producer.dashboard',
            'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf($this->labelFormat, 'home', $translator->trans('dashboard')),
        ));
        $menu[$menuName]->addChild('Orders', array(
            'uri'             => '#',
            // 'route'           => 'open_miam_miam.admin.producer.list_products',
            // 'routeParameters' => array('id' => $object->getId()),
            'label'           => sprintf($this->labelFormat, 'shopping-cart', $translator->trans('orders')),
        ));
        $menu[$menuName]->addChild('Products', array(
            'route'           => 'open_miam_miam.admin.producer.list_products',
            'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf($this->labelFormat, 'list', $translator->trans('products')),
        ));
        $menu[$menuName]->addChild('Calendar', array(
            'uri'             => '#',
            // 'route'           => 'open_miam_miam.admin.producer.list_products',
            // 'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf($this->labelFormat, 'time', $translator->trans('calendar')),
        ));
        $menu[$menuName]->addChild('Managers', array(
            'uri'             => '#',
            // 'route'           => 'open_miam_miam.admin.producer.list_products',
            // 'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf($this->labelFormat, 'lock', $translator->trans('managers')),
        ));
        $menu[$menuName]->addChild('Producer infos', array(
            'uri'             => '#',
            // 'route'           => 'open_miam_miam.admin.producer.list_products',
            // 'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf($this->labelFormat, 'user', $translator->trans('producer.infos')),
        ));
    }
}