<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Form\DataTransformer\ProductToIdentifierDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class HiddenProductType extends AbstractType
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager Entity Manager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(
            new ProductToIdentifierDataTransformer($this->entityManager)
        );
    }

    /**
     * @see AbstractType
     */
    public function getParent()
    {
        return 'hidden';
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_hidden_product';
    }
}