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

use Isics\Bundle\OpenMiamMiamBundle\Model\Cart\Cart;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CartType extends AbstractType implements EventSubscriberInterface
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('items', CollectionType::class, array('entry_type' => CartItemType::class, 'allow_delete' => true));
        $builder->add('update', SubmitType::class);
        $builder->add('checkout', SubmitType::class);

        $builder->addEventSubscriber($this);
    }

    /**
     * @see AbstractType
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => Cart::class));
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
        $transformer = new NumberToLocalizedStringTransformer();

        $data = $event->getData();
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $idx => $_data) {
                if (isset($_data['quantity']) && $transformer->reverseTransform($_data['quantity']) <= 0) {
                    unset($data['items'][$idx]);
                }
            }
        }

        $event->setData($data);
    }
}
