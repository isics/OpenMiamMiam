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

class CartType extends AbstractType implements EventSubscriberInterface
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('items', 'collection', array('type' => new CartItemType(), 'allow_delete' => true));
        $builder->add('Update', 'submit');
        $builder->add('Checkout', 'submit');

        $builder->addEventSubscriber($this);
    }

    /**
     * @see AbstractType
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Isics\Bundle\OpenMiamMiamBundle\Model\Cart',
            'cascade_validation' => true,
        ));
    }

    /**
     *
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SUBMIT => 'preSubmit');
    }

    /**
     * Process values before submit
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $idx => $_data) {
                if (isset($_data['quantity']) && $_data['quantity'] <= 0) {
                    unset($data['items'][$idx]);
                }
            }
        }

        $event->setData($data);
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_cart';
    }
}
