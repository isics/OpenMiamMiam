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

class AssociationType extends AbstractType
{
    /**
     * Builds the form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text')
            ->add('closingDelay', 'integer')
            ->add('openingDelay', 'integer')
            ->add('defaultCommission', 'number')
            ->add('address1', 'text', array(
                'required' => false
            ))
            ->add('address2', 'text', array(
                'required' => false
            ))
            ->add('zipcode', 'text', array(
                'required' => false
            ))
            ->add('city', 'text', array(
                'required' => false
            ))
            ->add('phoneNumber1', 'text', array(
                'required' => false
            ))
            ->add('phoneNumber2', 'text', array(
                'required' => false
            ))
            ->add('website', 'url', array(
                'required' => false
            ))
            ->add('facebook', 'url', array(
                'required' => false
            ))
            ->add('presentation', 'textarea', array(
                'required' => false
            ));
    }

    /**
     *
     * @param array $options
     * @return multitype:string
     */
    public function getDefaultOptions(array $options)
    {
        return array('data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Entity\Association');
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return 'open_miam_miam_association';
    }
}
