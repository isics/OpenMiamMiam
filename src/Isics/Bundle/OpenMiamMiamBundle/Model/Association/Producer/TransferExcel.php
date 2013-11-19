<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\Association\Producer;

use Isics\Bundle\OpenMiamMiamBundle\Model\Association\ProducersTransfer;
use Symfony\Component\Translation\Translator;

class TransferExcel
{
    /**
     * @var \PHPExcel
     */
    protected $excel;

    /**
     * @var \Symfony\Component\Translation\Translator
     */
    protected $translator;

    /**
     * Constructor
     *
     * @param \PHPExcel                                 $excel
     * @param \Symfony\Component\Translation\Translator $translator
     */
    public function __construct(\PHPExcel $excel, Translator $translator)
    {
        $this->excel      = $excel;
        $this->translator = $translator;
    }

    /**
     * Build document
     *
     * @param ProducersTransfer $producersTransfer
     */
    public function generate(ProducersTransfer $producersTransfer)
    {
        $this->excel->setActiveSheetIndex(0);
        $sheet = $this->excel->getActiveSheet();

        // Branch occurrence name and date
        $branchOccurrenceNumber = 1;

        foreach ($producersTransfer->getBranchOccurrences() as $branchOccurrence) {
            $sheet->setCellValue(
                $this->getColumnNameForNumber($branchOccurrenceNumber).'1',
                $branchOccurrence->getBranch()->getName()
            );
            $sheet->setCellValue(
                $this->getColumnNameForNumber($branchOccurrenceNumber).'2',
                $branchOccurrence->getEnd()->format('d/m/Y')
            );

            ++$branchOccurrenceNumber;
        }

        // producer total
        $sheet->setCellValue(
            $this->getColumnNameForNumber($branchOccurrenceNumber).'1',
            $this->translator->trans('excel.association.producer.transfer.total')
        );

        $currentLine = 3;

        foreach ($producersTransfer->getProducers() as $producer) {
            $sheet->setCellValue(
                'A'.(string)$currentLine,
                $producer->getName()
            );

            $branchOccurrenceNumber = 1;

            foreach ($producersTransfer->getBranchOccurrences() as $branchOccurrence) {
                $value = $producersTransfer->getData($producer->getId(), $branchOccurrence->getId());

                if ($value == 0.0) {
                    $value = null;
                }

                $sheet->setCellValue(
                    $this->getColumnNameForNumber($branchOccurrenceNumber).(string)$currentLine,
                    null !== $value ? $value : '-'
                );

                ++$branchOccurrenceNumber;
            }

            // Total for producer
            $sheet->setCellValue(
                $this->getColumnNameForNumber($branchOccurrenceNumber).(string)$currentLine,
                $producersTransfer->getTotalForProducerId($producer->getId())
            );

            ++$currentLine;
        }

        $sheet->setCellValue(
            'A'.(string)$currentLine,
            $this->translator->trans('excel.association.producer.transfer.total')
        );

        // Branch occurrence total
        $branchOccurrenceNumber = 1;

        foreach ($producersTransfer->getBranchOccurrences() as $branchOccurrence) {
            $sheet->setCellValue(
                $this->getColumnNameForNumber($branchOccurrenceNumber).(string)$currentLine,
                $producersTransfer->getTotalForBranchOccurrenceId($branchOccurrence->getId())
            );

            ++$branchOccurrenceNumber;
        }
    }

    /**
     * Returns Excel column name representation (A, AB, AAB...) for column index
     *
     * @param int $number
     *
     * @return string
     */
    private function getColumnNameForNumber($number)
    {
        if ((int)$number < 26) {
            return chr((int)$number + 65);
        }
        else {
            return $this->getColumnNameForNumber(floor($number / 26) - 1).chr($number%26 + 65);
        }
    }

    /**
     * Get excel
     *
     * @return \PHPExcel
     */
    public function getExcel()
    {
        return $this->excel;
    }

}