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
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var Translator $translator
     */
    protected $translator;



    /**
     * Contructs object
     *
     * @param mixed               $resource
     * @param RouterInterface     $router
     * @param TranslatorInterface $translator
     */
    public function __construct($resource, RouterInterface $router, TranslatorInterface $translator)
    {
        $this->resource = $resource;
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * Returns string representation of object
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s (%s)', $this->getName(), $this->translator->trans($this->getType()));
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
