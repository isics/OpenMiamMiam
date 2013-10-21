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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Newsletter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NewsletterType 
{
    /**
     * Builds the form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('recipientType', 'choice', array(
                    'choices' => array(
                    ),
                    'expanded' => true,
                ))
                ->add('Object', 'text')
                ->add('message', 'textarea')
                ->add('save', 'submit');
    }
    
    /**
     * @see AbstractType
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Entity\Newsletter',
        ));
    }
    
    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_super_newsletter';
    }
}