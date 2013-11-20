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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AssociationHasProducerType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('commission', 'number', array('precision' => 2, 'required' => false))
            ->add('producer', new ProducerHasBranchType())
            ->add('save', 'submit');
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_association_has_producer';
    }
}
