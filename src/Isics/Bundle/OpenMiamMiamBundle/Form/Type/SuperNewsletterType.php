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

use Doctrine\ORM\EntityRepository;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Newsletter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SuperNewsletterType extends AbstractType
{
    /**
     * Builds the form
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('recipientType', 'choice', array(
                'choices' => array(
                    Newsletter::RECIPIENT_TYPE_ALL      => 'recipient.type.all',
                    Newsletter::RECIPIENT_TYPE_CONSUMER => 'recipient.type.consumers',
                    Newsletter::RECIPIENT_TYPE_PRODUCER => 'recipient.type.producers',
                ),
                'multiple' => false,
                'expanded' => true,
            ))
            ->add('branches', 'entity', array(
                'class'         => 'IsicsOpenMiamMiamBundle:Branch',
                'property'      => 'nameWithAssociation',
                'empty_value'   => '',
                'multiple'      => true,
                'expanded'      => true,
                'by_reference'  => false,
                'required'      => true,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('b')
                        ->addOrderBy('b.association')
                        ->addOrderBy('b.city');
                },
            ))
            ->add('withoutBranch', 'checkbox', array('required' => false))
            ->add('subject', 'text')
            ->add('body', 'textarea')
            ->add('send', 'submit');
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