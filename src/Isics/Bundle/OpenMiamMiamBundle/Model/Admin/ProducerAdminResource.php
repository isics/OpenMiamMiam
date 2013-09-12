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
     * @param Producer $producer
     * @param RouterInterface $router
     */
    public function __construct(Producer $producer, RouterInterface $router)
    {
        parent::__construct($producer, $router);
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

    /**
     * Returns true if object is equal to resource
     *
     * @param mixed $object
     *
     * @return boolean
     */
    public function equals($object)
    {
        return get_class($object) === get_class($this->resource) && $object->getId() === $this->resource->getId();
    }
}
