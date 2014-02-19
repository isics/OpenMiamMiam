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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CartItemType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('product', 'open_miam_miam_hidden_product');
        $builder->add('quantity', 'number');
        if ($options['submit_button']) {
            $builder->add('add', 'submit');
        }
    }

    /**
     * @see AbstractType
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'    => 'Isics\Bundle\OpenMiamMiamBundle\Model\Cart\CartItem',
            'submit_button' => false,
        ));
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_cart_item';
    }
}
