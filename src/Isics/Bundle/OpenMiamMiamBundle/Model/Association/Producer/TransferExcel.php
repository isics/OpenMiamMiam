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

use Isics\Bundle\OpenMiamMiamBundle\Model\Association\ProducerTransfer;

class TransferExcel
{
    /**
     * @var \PHPExcel
     */
    protected $excel;

    /**
     * @var ProducerTransfer
     */
    protected $producerTransfer;

    /**
     * Constructor
     *
     * @param \PHPExcel $excel
     */
    public function __construct(\PHPExcel $excel)
    {
        $this->excel = $excel;
    }

    /**
     * Set data for Excel generation
     *
     * @param $producerTransferData
     */
    public function setProducerTransfer(ProducerTransfer $producerTransfer)
    {
        $this->producerTransfer = $producerTransfer;
    }

    /**
     * Build document
     */
    protected function build()
    {
        $this->excel->setActiveSheetIndex(0);
        $sheet = $this->excel->getActiveSheet();

        // Branch occurrence name and date
        $branchOccurrenceNumber = 1;

        foreach ($this->producerTransfer->getBranchOccurrences() as $branchOccurrence) {
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

        $currentLine = 3;

        foreach ($this->producerTransfer->getProducers() as $producer) {
            $sheet->setCellValue(
                'A'.(string)$currentLine,
                $producer->getName()
            );

            $branchOccurrenceNumber = 1;

            foreach ($this->producerTransfer->getBranchOccurrences() as $branchOccurrence) {
                $value = $this->producerTransfer->getData($producer->getId(), $branchOccurrence->getId());

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
                $this->producerTransfer->getTotalForProducerId($producer->getId())
            );

            ++$currentLine;
        }

        // Branch occurrence total
        $branchOccurrenceNumber = 1;

        foreach ($this->producerTransfer->getBranchOccurrences() as $branchOccurrence) {
            $sheet->setCellValue(
                $this->getColumnNameForNumber($branchOccurrenceNumber).(string)$currentLine,
                $this->producerTransfer->getTotalForBranchOccurrenceId($branchOccurrence->getId())
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
     * Render document
     *
     * @param string $filename
     */
    public function render($filename)
    {
        $this->build();

        ob_start();
        $writer = new \PHPExcel_Writer_Excel2007($this->excel);
        $writer->save('php://output');
        return ob_get_clean();
    }

}