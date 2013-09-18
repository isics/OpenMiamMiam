<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type\Admin;

use Doctrine\ORM\EntityRepository;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

// @todo (all branches selected by default)
// @todo (only next years)
class ProductType extends AbstractType
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
        $product = $options['data'];

        $builder->add('name', 'text')
                ->add('ref', 'text')
                ->add('category', 'entity', array(
                    'class' => 'IsicsOpenMiamMiamBundle:Category',
                    'property' => 'name',
                    'empty_value' => '',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                    },
                ))
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
                ->add('branches', 'entity', array(
                    'class' => 'IsicsOpenMiamMiamBundle:Branch',
                    'property' => 'name',
                    'empty_value' => '',
                    'multiple' => true,
                    'expanded' => true,
                    'by_reference' => false,
                    'query_builder' => function(EntityRepository $er) use ($product) {
                        return $er->getBranchesForProducerQueryBuilder($product->getProducer());
                    },
                ))
                ->add('save', 'submit');

        if (null !== $product->getImage()) {
            $builder->add('deleteImage', 'checkbox', array(
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
        return 'open_miam_miam_admin_product';
    }
}
