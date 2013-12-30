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

class SuperProducerType extends AbstractType
{
    /**
     * Builds the form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('ownerEmail', 'text')
            ->add('associations', 'entity', array(
                'class'        => 'IsicsOpenMiamMiamBundle:Association',
                'property'     => 'name',
                'empty_value'  => '',
                'multiple'     => true,
                'expanded'     => true,
                'by_reference' => false,
            ))
            ->add('save', 'submit');
    }

    /**
     *
     * @param array $options
     * @return multitype:string
     */
    public function getDefaultOptions(array $options)
    {
        return array('data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Model\Producer\ProducerWithOwner');
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return 'open_miam_miam_super_producer';
    }
}
