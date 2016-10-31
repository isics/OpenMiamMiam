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

use Isics\Bundle\OpenMiamMiamBundle\Model\ProducerAttendance\ProducerBranchOccurrenceAttendance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProducerBranchOccurrenceAttendanceType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('attendance', ChoiceType::class, array(
            'expanded' => true,
            'choices' => array(
                'attendance.yes' => ProducerBranchOccurrenceAttendance::ATTENDANCE_YES,
                'attendance.no' => ProducerBranchOccurrenceAttendance::ATTENDANCE_NO,
                'attendance.unknown' => ProducerBranchOccurrenceAttendance::ATTENDANCE_UNKNOWN
            ),
            'choices_as_values' => true,
        ));
    }

    /**
     * @see AbstractType
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => ProducerBranchOccurrenceAttendance::class));
    }
}
