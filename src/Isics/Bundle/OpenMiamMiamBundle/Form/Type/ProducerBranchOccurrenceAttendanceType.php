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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProducerBranchOccurrenceAttendanceType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('attendance', 'choice', array(
            'expanded' => true,
            'choices' => array(
                ProducerBranchOccurrenceAttendance::ATTENDANCE_YES     => 'attendance.yes',
                ProducerBranchOccurrenceAttendance::ATTENDANCE_NO      => 'attendance.no',
                ProducerBranchOccurrenceAttendance::ATTENDANCE_UNKNOWN => 'attendance.unknown'
            ),
        ));
    }

    /**
     * @see AbstractType
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Model\ProducerAttendance\ProducerBranchOccurrenceAttendance',
        ));
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_producer_branch_occurrence_attendance';
    }
}
