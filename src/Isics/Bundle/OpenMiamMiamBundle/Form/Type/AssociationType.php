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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

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
        $builder
            ->add('name', TextType::class)
            ->add('closingDelay', IntegerType::class)
            ->add('openingDelay', IntegerType::class)
            ->add('defaultCommission', NumberType::class)
            ->add('address1', TextType::class, array('required' => false))
            ->add('address2', TextType::class, array('required' => false))
            ->add('zipcode', TextType::class, array('required' => false))
            ->add('city', TextType::class, array('required' => false))
            ->add('phoneNumber1', TextType::class, array('required' => false))
            ->add('phoneNumber2', TextType::class, array('required' => false))
            ->add('website', UrlType::class, array('required' => false))
            ->add('facebook', UrlType::class, array('required' => false))
            ->add('presentation', TextareaType::class, array('required' => false));
    }

    /**
     * @see AbstractType
     */
    public function getDefaultOptions(array $options)
    {
        return array('data_class' => Association::class);
    }
}
