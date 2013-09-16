<?php

/*
 * This file is part of the isicsEntityToIdentifierDataTransformerBundle project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\EntityToIdentifierDataTransformerBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EntityToIdentifyingStringDataTransformer implements DataTransformerInterface
{
    /**
     * @var string $class
     */
    private $class;

    /**
     * @var string $separator
     */
    private $separator;

    /**
     * Constructor
     *
     * @param string $class     Class
     * @param string $separator Separator
     */
    public function __construct($class, $separator = ';;')
    {
        $this->class     = $class;
        $this->separator = $separator;
    }

    /**
     * Transforms an Entity into an identifiying string.
     *
     * @param Boolean $value Boolean value.
     *
     * @return string String value.
     *
     * @throws TransformationFailedException If the given value is not a Boolean.
     */
    public function transform($entity)
    {
        if (null === $value) {
            return null;
        }

        if (!is_bool($value)) {
            throw new TransformationFailedException('Expected a Boolean.');
        }

        return true === $value ? $this->trueValue : null;
    }

    /**
     * Transforms a string into a Boolean.
     *
     * @param string $value String value.
     *
     * @return Boolean Boolean value.
     *
     * @throws TransformationFailedException If the given value is not a string.
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return false;
        }

        if (!is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        return true;
    }
}