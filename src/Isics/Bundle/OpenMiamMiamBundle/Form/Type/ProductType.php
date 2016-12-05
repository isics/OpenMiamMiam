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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $builder->add('name', TextType::class)
                ->add('category', EntityType::class, array(
                    'class' => 'IsicsOpenMiamMiamBundle:Category',
                    'choice_label' => 'indentedName',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->getNodesHierarchyQueryBuilder()->andWhere('node.lvl > 0');
                    },
                ))
                ->add('ref', TextType::class)
                ->add('isBio', CheckboxType::class, array(
                    'required' => false
                ))
                ->add('isOfTheMoment', CheckboxType::class, array(
                    'required' => false
                ))
                ->add('imageFile', FileType::class, array(
                    'required' => false
                ))
                ->add('description', TextareaType::class, array(
                    'required' => false
                ))
                ->add('buyingUnit', ChoiceType::class, array(
                    'placeholder' => 'Without unit',
                    'choices' => $this->buyingUnits,
                    'required' => false,
                    'choices_as_values' => true,
                ))
                ->add('allowDecimalQuantity', CheckboxType::class, array(
                    'required' => false
                ))
                ->add('hasNoPrice', CheckboxType::class, array(
                    'required' => false
                ))
                ->add('price', TextType::class, array(
                    'required' => false
                ))
                ->add('priceInfo', TextType::class, array(
                    'required' => false
                ))
                ->add('availability', ChoiceType::class, array(
                    'choices' => array(
                        'availability.available' => Product::AVAILABILITY_AVAILABLE,
                        'availability.in_stock' => Product::AVAILABILITY_ACCORDING_TO_STOCK,
                        'availability.available_at' => Product::AVAILABILITY_AVAILABLE_AT,
                        'availability.unavailable' => Product::AVAILABILITY_UNAVAILABLE,
                    ),
                    'expanded' => true,
                    'choices_as_values' => true,
                ))
                ->add('stock', TextType::class, array(
                    'required' => false
                ))
                ->add('availableAt', DateType::class, array(
                    'required' => false
                ))
                ->addEventSubscriber($this)
                ->add('save', SubmitType::class);
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
            $form->add('branches', EntityType::class, array(
                'class' => 'IsicsOpenMiamMiamBundle:Branch',
                'choice_label' => 'name',
                'placeholder' => '',
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'query_builder' => function(EntityRepository $er) use ($producer) {
                    return $er->getForProducerQueryBuilder($producer);
                },
            ));
        }

        if (null !== $product->getImage()) {
            $form->add('deleteImage', CheckboxType::class, array(
                'required' => false
            ));
        }
    }

    /**
     * @see AbstractType
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Entity\Product',
        ));
    }
}
