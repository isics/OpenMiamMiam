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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $typeChoices = array(
            'payment.cash' => Payment::TYPE_CASH,
            'payment.cheque' => Payment::TYPE_CHEQUE
        );

        if (!$options['without_amount']) {
            $builder->add('amount', MoneyType::class);
            $typeChoices['payment.transfer'] = Payment::TYPE_TRANSFER;
        }

        $builder->add('type', ChoiceType::class, array(
            'choices'  => $typeChoices,
            'expanded' => false,
            'choices_as_values' => true,
        ));

        if ($options['with_submit']) {
            $builder->add('save', SubmitType::class);
        }
    }

    /**
     * @see AbstractType
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'     => Payment::class,
            'without_amount' => true,
            'with_submit'    => true
        ));
    }
}
