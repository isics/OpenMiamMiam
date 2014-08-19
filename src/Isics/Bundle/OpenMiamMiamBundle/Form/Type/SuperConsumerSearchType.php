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
use Symfony\Component\Translation\TranslatorInterface;

class SuperConsumerSearchType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('get')
            ->add(
                'ref',
                'integer',
                array(
                    'required' => false
                )
            )
            ->add(
                'lastName',
                'text',
                array(
                    'required' => false
                )
            )
            ->add(
                'firstName',
                'text',
                array(
                    'required' => false
                )
            )
            ->add('deleted', 'checkbox', ['required' => false])
            ;
    }

    /**
     * @see AbstractType
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Model\Consumer\SuperConsumerFilter',
        ));
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_super_consumer_search';
    }
} 