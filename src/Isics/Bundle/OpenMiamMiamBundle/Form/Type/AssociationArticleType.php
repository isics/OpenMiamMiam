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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Article;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssociationArticleType extends AbstractType implements EventSubscriberInterface
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class)
                ->add('body', TextareaType::class)
                ->add('isPublished', CheckboxType::class, array('required' => false))
                ->add('save', SubmitType::class)
                ->addEventSubscriber($this);
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $article = $event->getData();

        if (null === $article) {
            return;
        }

        $association = $article->getAssociation();
        if (null !== $association) {
            $form->add('branches', EntityType::class, array(
                'class' => Branch::class,
                'choice_label' => 'name',
                'placeholder' => '',
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'query_builder' => function(EntityRepository $er) use ($association) {
                    return $er->createQueryBuilder('b')
                        ->where('b.association = :association')
                        ->setParameter('association', $association);
                },
            ));
        }
    }

    /**
     * @see AbstractType
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => Article::class));
    }
}
