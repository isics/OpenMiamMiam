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

use FOS\UserBundle\Form\Type\UsernameFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('name', TextType::class)
            ->add('owner', UsernameFormType::class)
            ->add('associations', EntityType::class, array(
                'class'        => 'IsicsOpenMiamMiamBundle:Association',
                'choice_label' => 'name',
                'placeholder'  => '',
                'multiple'     => true,
                'expanded'     => true,
                'by_reference' => false,
            ))
            ->add('save', SubmitType::class);
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
}
