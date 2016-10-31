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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BranchType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('welcomeText', TextareaType::class)
            ->add('presentation', TextareaType::class)
            ->add('address1', TextType::class, array('required' => false))
            ->add('address2', TextType::class, array('required' => false))
            ->add('zipcode', TextType::class, array('required' => false))
            ->add('city', TextType::class, array('required' => false))
            ->add('departmentNumber', TextType::class)
            ->add('phoneNumber1', TextType::class, array('required' => false))
            ->add('phoneNumber2', TextType::class, array('required' => false))
            ->add('website', UrlType::class, array('required' => false))
            ->add('facebook', UrlType::class, array('required' => false))
            ->add('save', SubmitType::class);
    }

    /**
     * @see AbstractType
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => Branch::class));
    }
}
