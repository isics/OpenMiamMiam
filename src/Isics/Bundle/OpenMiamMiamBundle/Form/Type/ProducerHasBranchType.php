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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;

class ProducerHasBranchType extends AbstractType implements EventSubscriberInterface
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this);
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => Producer::class));
    }

    /**
     *
     */
    public function onPreSetData(FormEvent $event)
    {
        if (null === $event->getData()) {
            return;
        }
        $associationHasProducer = $event->getForm()->getParent()->getData();

        $event->getForm()->add(
            'branches',
            'entity',
            array(
                'multiple' => true,
                'expanded' => true,
                'class' => 'IsicsOpenMiamMiamBundle:Branch',
                'choices' => $associationHasProducer->getAssociation()->getBranches(),
                'property' => 'name',
                'by_reference' => true
            )
        );
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_producer_has_branch';
    }

    /**
     * Get subscribe events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'onPreSetData');
    }
}
