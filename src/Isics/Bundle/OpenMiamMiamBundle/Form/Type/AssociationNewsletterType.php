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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
                ->add('save', 'submit')
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
    
        if (null === $newsletter) {
            return;
        }
    
        $association = $newsletter->getAssociation();
        if (null !== $association) {
            $form->add('branches', 'entity', array(
                    'class' => 'IsicsOpenMiamMiamBundle:Branch',
                    'property' => 'name',
                    'empty_value' => '',
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
        return 'open_miam_miam_association_newsletter';
    }
}