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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AssociationHasProducerType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $associationHasProducer = $options['data'];

        $builder
            ->add('commission', 'number', array('precision' => 2, 'required' => false))
            ->add('branches', 'entity', array(
                'multiple'     => true,
                'expanded'     => true,
                'class'        => 'IsicsOpenMiamMiamBundle:Branch',
                'choices'      => $associationHasProducer->getAssociation()->getBranches(),
                'property'     => 'name',
                'by_reference' => false
            ))
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Entity\AssociationHasProducer'
        ));
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_association_has_producer';
    }
}
