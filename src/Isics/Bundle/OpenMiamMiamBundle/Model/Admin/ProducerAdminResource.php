<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\Admin;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\AdminResource;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class AdminResource
 */
final class ProducerAdminResource extends AdminResource
{
    /**
     * Constructs object
     *
     * @param RouterInterface $router
     * @param Producer $producer
     */
    public function __construct(RouterInterface $router, Producer $producer)
    {
        parent::__construct($router);
        $this->resource = $producer;
    }

    /**
     * Returns the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->resource->getName();
    }

    /**
     * Returns the type
     *
     * @return string
     */
    public function getType()
    {
        return 'producer';
    }

    /**
     * Returns the route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->router->generate('open_miam_miam.admin.producer.dashboard', array('id' => $this->resource->getId()));
    }
}
