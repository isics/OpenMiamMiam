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
        $builder->add('name', 'text')
                ->add('profileImageFile', 'file', array(
                        'required' => false
                ))
                ->add('presentationImageFile', 'file', array(
                        'required' => false
                ))
                ->add('address1', 'text', array(
                    'required' => false
                ))
                ->add('address2', 'text', array(
                    'required' => false
                ))
                ->add('zipcode', 'text', array(
                    'required' => false
                ))
                ->add('city', 'text', array(
                    'required' => false
                ))
                ->add('phone1', 'text', array(
                    'required' => false
                ))
                ->add('phone2', 'text', array(
                    'required' => false
                ))
                ->add('website', 'url', array(
                    'required' => false
                ))
                ->add('facebook', 'url', array(
                    'required' => false
                ))
                ->add('presentation', 'textarea', array(
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
            $form->add('deleteProfileImage', 'checkbox', array('required' => false));
        }
        if (null !== $producer->getPresentationImage()) {
            $form->add('deletePresentationImage', 'checkbox', array('required' => false));
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

    /**
     *
     * @return string
     */
    public function getName()
    {
        return 'open_miam_miam_producer';
    }
}
