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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('recipientType', ChoiceType::class, array(
                'choices' => array(
                    Newsletter::RECIPIENT_TYPE_ALL      => 'recipient.type.all',
                    Newsletter::RECIPIENT_TYPE_CONSUMER => 'recipient.type.consumers',
                    Newsletter::RECIPIENT_TYPE_PRODUCER => 'recipient.type.producers',
                ),
                'multiple' => false,
                'expanded' => true,
            ))
            ->add('branches', EntityType::class, array(
                'class'         => 'IsicsOpenMiamMiamBundle:Branch',
                'choice_label'  => 'nameWithAssociation',
                'placeholder'   => '',
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
            ->add('withoutBranch', CheckboxType::class, array('required' => false))
            ->add('subject', TextType::class)
            ->add('body', TextareaType::class)
            ->add('send', SubmitType::class);
    }

    /**
     * @see AbstractType
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Entity\Newsletter',
        ));
    }
}
