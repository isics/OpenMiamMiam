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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class AssociationProducerExportTransferType extends AbstractType
{
    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * Constructor
     *
     * @param TranslatorInterface $translator Translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Builds the form
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array();

        // Start at last month
        $date = new \DateTime('first day of this month midnight');

        $formatter = new \IntlDateFormatter(
            \Locale::getDefault(),
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            null,
            'MMMM yyyy'
        );

        // Store last 12 previous months
        for ($i = 0 ; $i < 12 ; $i++) {
            $choices[$date->format('Y-m')] = $this->translator->trans(
                'admin.association.producers.list.export.choice',
                array('%month%' => $formatter->format($date))
            );

            $date->modify('first day of previous month midnight');
        }

        $builder
            ->add('month', ChoiceType::class, array('choices' => $choices))
            ->add('export', SubmitType::class);
    }
}
