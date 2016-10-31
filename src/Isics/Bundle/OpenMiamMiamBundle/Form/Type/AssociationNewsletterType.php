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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Newsletter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssociationNewsletterType extends AbstractType
{
    /**
     * Builds the form
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $association = $options['data']->getAssociation();

        $builder
            ->add('recipientType', ChoiceType::class, array(
                'choices' => array(
                    Newsletter::RECIPIENT_TYPE_ALL      => 'recipient.type.all',
                    Newsletter::RECIPIENT_TYPE_CONSUMER => 'recipient.type.consumers',
                    Newsletter::RECIPIENT_TYPE_PRODUCER => 'recipient.type.producers',
                ),
                'multiple' => false,
                'expanded' => true
            ))
            ->add('branches', EntityType::class, array(
                'class'         => Branch::class,
                'choice_label'  => 'name',
                'placeholder'   => '',
                'multiple'      => true,
                'expanded'      => true,
                'by_reference'  => false,
                'query_builder' => function(EntityRepository $er) use ($association) {
                    return $er->createQueryBuilder('b')
                        ->where('b.association = :association')
                        ->setParameter('association', $association);
                },
            ))
            ->add('subject', TextType::class)
            ->add('body', TextareaType::class)
            ->add('send', SubmitType::class);
    }

    /**
     * @see AbstractType
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => Newsletter::class));
    }
}
