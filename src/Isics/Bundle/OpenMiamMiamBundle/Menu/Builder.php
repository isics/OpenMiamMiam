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

use Isics\Bundle\OpenMiamMiamBundle\Manager\AdminManager;
use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\AdminResourceInterface;
use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\AssociationAdminResource;
use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\ProducerAdminResource;
use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\RelayAdminResource;
use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\SuperAdminResource;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Translation\TranslatorInterface;

class Builder
{
    /**
     * @var FactoryInterface $factory
     */
    protected $factory;

    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * @var AdminManager $adminManager
     */
    protected $adminManager;

    /**
     * @var string $labelFormat format for labels
     */
    protected $labelFormat = '<span class="glyphicon glyphicon-%s"></span> %s';

    /**
     * Constructor
     *
     * @param FactoryInterface    $factory    Factory
     * @param TranslatorInterface $translator Translator
     * @param AdminManager $adminManager
     */
    public function __construct(FactoryInterface $factory, TranslatorInterface $translator, AdminManager $adminManager)
    {
        $this->factory      = $factory;
        $this->translator   = $translator;
        $this->adminManager = $adminManager;
    }

    /**
     * Admin menu
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createAdminMenu()
    {
        $menu = $this->factory->createItem('root');

        foreach ($this->adminManager->findAvailableAdminResources() as $resource) {
            if ($resource instanceof SuperAdminResource) {
                $this->addSuperAdminMenuItems($menu, $resource);
            } else if ($resource instanceof AssociationAdminResource) {
                 $this->addAssociationAdminMenuItems($menu, $resource);
            } else if ($resource instanceof ProducerAdminResource) {
                $this->addProducerAdminMenuItems($menu, $resource);
            } else if ($resource instanceof RelayAdminResource) {
                $this->addRelayAdminMenuItems($menu, $resource);
            }
        }

        return $menu;
    }

    /**
     * Adds menu items for the super admin area
     *
     * @param ItemInterface          $menu     Root menu
     * @param AdminResourceInterface $resource Admin resource
     */
    protected function addSuperAdminMenuItems(ItemInterface $menu, AdminResourceInterface $resource)
    {
        $menuName = 'super';

        $menu->addChild($menuName, array(
            'route' => 'open_miam_miam.admin.super.show_dashboard',
            'label' => sprintf($this->translator->trans($resource->getType())),
        ));
        $menu[$menuName]->addChild('Dashboard', array(
            'route' => 'open_miam_miam.admin.super.show_dashboard',
            'label' => sprintf($this->labelFormat, 'home', $this->translator->trans('admin.super.menu.dashboard')),
        ));
        $menu[$menuName]->addChild('UserSwitch', array(
            'route' => 'open_miam_miam.admin.super.user_switch.list',
            'label' => sprintf($this->labelFormat, 'transfer', $this->translator->trans('admin.super.menu.user_switch')),
        ));
        $menu[$menuName]->addChild('News', array(
            'route' => 'open_miam_miam.admin.super.article.list',
            'label' => sprintf($this->labelFormat, 'bullhorn', $this->translator->trans('admin.super.menu.news')),
        ));
    }

    /**
     * Adds menu items for a association admin area
     *
     * @param ItemInterface          $menu     Root menu
     * @param AdminResourceInterface $resource Admin resource
     */
    protected function addAssociationAdminMenuItems(ItemInterface $menu, AdminResourceInterface $resource)
    {
        $association = $resource->getEntity();

        $menuName = sprintf('association%s', $association->getId());

        $menu->addChild($menuName, array(
            'route'           => 'open_miam_miam.admin.association.show_dashboard',
            'routeParameters' => array('id' => $association->getId()),
            'label'           => sprintf('%s (%s)', $association->getName(), $this->translator->trans($resource->getType())),
        ));
        $menu[$menuName]->addChild('Dashboard', array(
            'route'           => 'open_miam_miam.admin.association.show_dashboard',
            'routeParameters' => array('id' => $association->getId()),
            'label'           => sprintf($this->labelFormat, 'home', $this->translator->trans('admin.association.menu.dashboard')),
        ));
        $menu[$menuName]->addChild('Orders', array(
            'route'           => 'open_miam_miam.admin.association.sales_order.list',
            'routeParameters' => array('id' => $association->getId()),
            'label'           => sprintf($this->labelFormat, 'shopping-cart', $this->translator->trans('admin.association.menu.orders')),
        ));
        $menu[$menuName]->addChild('Branches', array(
            'route'           => 'open_miam_miam.admin.association.branch.list',
            'routeParameters' => array('id' => $association->getId()),
            'label'           => sprintf($this->labelFormat, 'pushpin', $this->translator->trans('admin.association.menu.branches')),
        ));
        $menu[$menuName]->addChild('Consumers', array(
            'route'           => 'open_miam_miam.admin.association.consumer.list',
            'routeParameters' => array('id' => $association->getId()),
            'label'           => sprintf($this->labelFormat, 'user', $this->translator->trans('admin.association.menu.consumers')),
        ));
        $menu[$menuName]->addChild('News', array(
            'route'           => 'open_miam_miam.admin.association.article.list',
            'routeParameters' => array('id' => $association->getId()),
            'label'           => sprintf($this->labelFormat, 'bullhorn', $this->translator->trans('admin.association.menu.news')),
        ));
    }

    /**
     * Adds menu items for a producer admin area
     *
     * @param ItemInterface          $menu     Root menu
     * @param AdminResourceInterface $resource Admin resource
     */
    protected function addProducerAdminMenuItems(ItemInterface $menu, AdminResourceInterface $resource)
    {
        $producer = $resource->getEntity();

        $menuName = sprintf('producer%s', $producer->getId());

        $menu->addChild($menuName, array(
            'route'           => 'open_miam_miam.admin.producer.show_dashboard',
            'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf('%s (%s)', $producer->getName(), $this->translator->trans($resource->getType())),
        ));
        $menu[$menuName]->addChild('Dashboard', array(
            'route'           => 'open_miam_miam.admin.producer.show_dashboard',
            'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf($this->labelFormat, 'home', $this->translator->trans('admin.producer.menu.dashboard')),
        ));
        $menu[$menuName]->addChild('Orders', array(
            'route'           => 'open_miam_miam.admin.producer.sales_order.list',
            'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf($this->labelFormat, 'shopping-cart', $this->translator->trans('admin.producer.menu.orders')),
        ));
        $menu[$menuName]->addChild('Products', array(
            'route'           => 'open_miam_miam.admin.producer.product.list',
            'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf($this->labelFormat, 'list', $this->translator->trans('admin.producer.menu.products')),
        ));
        $menu[$menuName]->addChild('Calendar', array(
            'route'           => 'open_miam_miam.admin.producer.calendar.edit',
            'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf($this->labelFormat, 'time', $this->translator->trans('admin.producer.menu.calendar')),
        ));
        // $menu[$menuName]->addChild('Managers', array(
        //     // 'route'           => 'open_miam_miam.admin.producer.product.list',
        //     // 'routeParameters' => array('id' => $producer->getId()),
        //     'label'           => sprintf($this->labelFormat, 'lock', $this->translator->trans('admin.producer.menu.managers')),
        // ));
        $menu[$menuName]->addChild('Producer infos', array(
            'route'           => 'open_miam_miam.admin.producer.edit',
            'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf($this->labelFormat, 'user', $this->translator->trans('admin.producer.menu.producer_infos')),
        ));
    }

    /**
     * Adds menu items for a relay admin area
     *
     * @param ItemInterface          $menu     Root menu
     * @param AdminResourceInterface $resource Admin resource
     */
    protected function addRelayAdminMenuItems(ItemInterface $menu, AdminResourceInterface $resource)
    {
        // @todo
    }
}
