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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

// @todo (only next years)
class ProductType extends AbstractType  implements EventSubscriberInterface
{
    /**
     * @var array $buyingUnits
     */
    protected $buyingUnits;



    /**
     * Constructs type
     *
     * @param array $buyingUnits
     */
    public function __construct(array $buyingUnits = array())
    {
        $this->buyingUnits = array_combine(array_values($buyingUnits), array_values($buyingUnits));
    }

    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text')
                ->add('category', 'entity', array(
                    'class' => 'IsicsOpenMiamMiamBundle:Category',
                    'property' => 'indentedName',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->getNodesHierarchyQueryBuilder();
                    },
                ))
                ->add('ref', 'text')
                ->add('isBio', 'checkbox', array(
                    'required' => false
                ))
                ->add('isOfTheMoment', 'checkbox', array(
                    'required' => false
                ))
                ->add('imageFile', 'file', array(
                    'required' => false
                ))
                ->add('description', 'textarea', array(
                    'required' => false
                ))
                ->add('buyingUnit', 'choice', array(
                    'empty_value' => 'Without unit',
                    'choices' => $this->buyingUnits,
                    'required' => false
                ))
                ->add('allowDecimalQuantity', 'checkbox', array(
                    'required' => false
                ))
                ->add('hasNoPrice', 'checkbox', array(
                    'required' => false
                ))
                ->add('price', 'text', array(
                    'required' => false
                ))
                ->add('priceInfo', 'text', array(
                    'required' => false
                ))
                ->add('availability', 'choice', array(
                    'choices' => array(
                        Product::AVAILABILITY_AVAILABLE => 'availability.available',
                        Product::AVAILABILITY_ACCORDING_TO_STOCK => 'availability.in_stock',
                        Product::AVAILABILITY_AVAILABLE_AT => 'availability.available_at',
                        Product::AVAILABILITY_UNAVAILABLE => 'availability.unavailable',
                    ),
                    'expanded' => true,
                ))
                ->add('stock', 'text', array(
                    'required' => false
                ))
                ->add('availableAt', 'date', array(
                    'required' => false
                ))
                ->addEventSubscriber($this)
                ->add('save', 'submit');
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
        $product = $event->getData();

        if (null === $product) {
            return;
        }

        $producer = $product->getProducer();
        if (null !== $producer) {
            $form->add('branches', 'entity', array(
                'class' => 'IsicsOpenMiamMiamBundle:Branch',
                'property' => 'name',
                'empty_value' => '',
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'query_builder' => function(EntityRepository $er) use ($producer) {
                    return $er->getForProducerQueryBuilder($producer);
                },
            ));
        }

        if (null !== $product->getImage()) {
            $form->add('deleteImage', 'checkbox', array(
                'required' => false
            ));
        }
    }

    /**
     * @see AbstractType
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Entity\Product',
        ));
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_product';
    }
}
