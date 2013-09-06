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

use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\AdminResourceInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class AdminResource
 */
abstract class AdminResource implements AdminResourceInterface
{
    /**
     * @var mixed $resource
     */
    protected $resource;

    /**
     * @var RouterInterface $router
     */
    protected $router;



    /**
     * Contructs object
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Returns string representation of object
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Returns resource
     *
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }
}
