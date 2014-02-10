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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Payment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PaymentType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $typeChoices = array(
            Payment::TYPE_CASH => 'payment.cash',
            Payment::TYPE_CHEQUE => 'payment.cheque'
        );

        if (!$options['without_amount']) {
            $builder->add('amount', 'money');
            $typeChoices[Payment::TYPE_TRANSFER] = 'payment.transfer';
        }

        $builder->add('type', 'choice', array(
                    'choices'  => $typeChoices,
                    'expanded' => false
                ));

        if ($options['with_submit']) {
            $builder->add('save', 'submit');
        }
    }

    /**
     * @see AbstractType
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'     => 'Isics\Bundle\OpenMiamMiamBundle\Entity\Payment',
            'without_amount' => true,
            'with_submit'    => true
        ));
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_payment';
    }
}
