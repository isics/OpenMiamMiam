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
     * @param FactoryInteface $factory
     * @param array           $options
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
        // @todo
    }

    /**
     * Adds menu items for a association admin area
     *
     * @param ItemInterface          $menu     Root menu
     * @param AdminResourceInterface $resource Admin resource
     */
    protected function addAssocationAdminMenuItems(ItemInterface $menu, AdminResourceInterface $resource)
    {
        // @todo
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
            'route'           => 'open_miam_miam.admin.producer.dashboard',
            'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf('%s (%s)', $producer->getName(), $this->translator->trans($resource->getType())),
        ));
        $menu[$menuName]->addChild('Dashboard', array(
            'route'           => 'open_miam_miam.admin.producer.dashboard',
            'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf($this->labelFormat, 'home', $this->translator->trans('admin.producer.menu.dashboard')),
        ));
        $menu[$menuName]->addChild('Orders', array(
            'route'           => 'open_miam_miam.admin.producer.list_sales_orders',
            'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf($this->labelFormat, 'shopping-cart', $this->translator->trans('admin.producer.menu.orders')),
        ));
        $menu[$menuName]->addChild('Products', array(
            'route'           => 'open_miam_miam.admin.producer.list_products',
            'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf($this->labelFormat, 'list', $this->translator->trans('admin.producer.menu.products')),
        ));
        $menu[$menuName]->addChild('Calendar', array(
            'uri'             => '#',
             'route'           => 'open_miam_miam.admin.producer.calendar',
             'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf($this->labelFormat, 'time', $this->translator->trans('admin.producer.menu.calendar')),
        ));
        $menu[$menuName]->addChild('Managers', array(
            'uri'             => '#',
            // 'route'           => 'open_miam_miam.admin.producer.list_products',
            // 'routeParameters' => array('id' => $producer->getId()),
            'label'           => sprintf($this->labelFormat, 'lock', $this->translator->trans('admin.producer.menu.managers')),
        ));
        $menu[$menuName]->addChild('Producer infos', array(
            'uri'             => '#',
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
