<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforms a product to and from an identifying string
 */
class ProductToIdentifierDataTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param EntityManager $objectManager Entity Manager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @see DataTransformerInterface
     */
    public function transform($value)
    {
        if (null === $value) {
            return '';
        }

        if (!$value instanceof Product) {
            throw new TransformationFailedException('Value must be an instance of Product!');
        }

        return (string) $value->getId();
    }

    /**
     * @see DataTransformerInterface
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        $product = $this->objectManager
            ->getRepository('IsicsOpenMiamMiamBundle:Product')
            ->find($value);

        if (null === $product) {
            throw new TransformationFailedException(sprintf('No product found with id %s!', $value));
        }

        return $product;
    }
}