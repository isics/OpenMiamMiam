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

use Isics\Bundle\OpenMiamMiamBundle\Entity\AssociationHasProducer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class AssociationHasProducerType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $associationHasProducer = $options['data'];

        $builder
            ->add('commission', NumberType::class, array('precision' => 2, 'required' => false))
            ->add('branches', EntityType::class, array(
                'multiple'     => true,
                'expanded'     => true,
                'class'        => Branch::class,
                'choices'      => $associationHasProducer->getAssociation()->getBranches(),
                'choice_label' => 'name',
                'by_reference' => false
            ))
            ->add('save', SubmitType::class);
    }

    /**
     * @see AbstractType
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => AssociationHasProducer::class));
    }
}
