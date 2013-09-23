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

use Isics\Bundle\OpenMiamMiamBundle\Form\Type\Admin\SalesOrderRowType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SalesOrderType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('salesOrderRows','collection', array('type' => new SalesOrderRowType()))
                ->add('save', 'submit');

    }

    /**
     * @see AbstractType
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder'));
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_admin_sales_order';
    }
}
