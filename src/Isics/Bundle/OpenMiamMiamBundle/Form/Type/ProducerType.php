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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ProducerType extends AbstractType implements EventSubscriberInterface
{
    /**
     * Builds the form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class)
                ->add('specialty', TextType::class)
                ->add('profileImageFile', FileType::class, array(
                        'required' => false
                ))
                ->add('presentationImageFile', FileType::class, array(
                        'required' => false
                ))
                ->add('address1', TextType::class, array(
                    'required' => false
                ))
                ->add('address2', TextType::class, array(
                    'required' => false
                ))
                ->add('zipcode', TextType::class, array(
                    'required' => false
                ))
                ->add('city', TextType::class, array(
                    'required' => false
                ))
                ->add('phoneNumber1', TextType::class, array(
                    'required' => false
                ))
                ->add('phoneNumber2', TextType::class, array(
                    'required' => false
                ))
                ->add('website', UrlType::class, array(
                    'required' => false
                ))
                ->add('facebook', UrlType::class, array(
                    'required' => false
                ))
                ->add('presentation', TextareaType::class, array(
                        'required' => false
                ))
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
        $producer = $event->getData();

        if (null !== $producer->getProfileImage()) {
            $form->add('deleteProfileImage', CheckboxType::class, array('required' => false));
        }
        if (null !== $producer->getPresentationImage()) {
            $form->add('deletePresentationImage', CheckboxType::class, array('required' => false));
        }
    }

    /**
     *
     * @param array $options
     * @return multitype:string
     */
    public function getDefaultOptions(array $options)
    {
        return array('data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Entity\Producer');
    }
}
