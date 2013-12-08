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

use Isics\Bundle\OpenMiamMiamBundle\Document\ProducersDepositWithdrawalTransfer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Symfony\Component\Translation\Translator;

class DepositWithdrawalExcel
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
     * @var string $currency
     */
    protected $currency;


    /**
     * Constructor
     *
     * @param \PHPExcel $excel
     * @param \Symfony\Component\Translation\Translator $translator
     * @param $currency
     */
    public function __construct(\PHPExcel $excel, Translator $translator, $currency)
    {
        $this->excel        = $excel;
        $this->translator   = $translator;
        $this->currency     = $currency;
    }

    /**
     * Build document
     *
     * @param ProducersDepositWithdrawalTransfer $producersTransfer
     * @param BranchOccurrence                   $branchOccurrence
     */
    public function generate(ProducersDepositWithdrawalTransfer $producersTransfer, BranchOccurrence $branchOccurrence)
    {
        $this->excel->setActiveSheetIndex(0);
        $sheet = $this->excel->getActiveSheet();
        $sheet->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

        $formatter = new \NumberFormatter($this->translator->getLocale(), \NumberFormatter::CURRENCY);
        $intl = new \IntlDateFormatter($this->translator->getLocale(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'MMMM Y');

        $boldStyle = array(
            'font' => array(
                'bold' => true,
            )
        );

        $centerStyle = array(
            'alignment'=>array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        );

        $rightStyle = array(
            'alignment'=>array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            )
        );

        $borderStyle = array(
            'borders' => array(
                'outline' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                ),
            )
        );

        $currentLine = 1;

        // Merge cells for document title
        $sheet->mergeCells('A1:'.'E1');
        $sheet->setCellValue(
            'A1',
            $branchOccurrence->getBranch()->getName()."\n\n".
            $branchOccurrence->getEnd()->format('d/m/Y')
        );
        $sheet->getStyle('A1')->    getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1')->applyFromArray(array_merge($centerStyle, $boldStyle));
        $sheet->getRowDimension('1')
            ->setRowHeight(50);

        $currentLine++;

        foreach ($producersTransfer->getProducers() as  $producerId => $producerName) {

            $sheet->mergeCells('A'.(string)$currentLine.':'.'E'.(string)$currentLine);

            // Producer name
            $sheet->setCellValue(
                'A'.(string)$currentLine,
                $producerName
            );
            $sheet->getStyle('A'.(string)$currentLine)->applyFromArray(array_merge($centerStyle, $boldStyle));
            $startLine = (string)$currentLine;
            $currentLine++;

            // title for product ref
            $sheet->setCellValue(
                'A'.(string)$currentLine,
                $this->translator->trans('excel.association.sales_order.deposit_withdrawal.product_ref')
            );
            $sheet->getStyle(
                'A'.(string)$currentLine
            )->applyFromArray(array_merge($centerStyle, $boldStyle));

            // title for product name
            $sheet->setCellValue(
                'B'.(string)$currentLine,
                $this->translator->trans('excel.association.sales_order.deposit_withdrawal.product_name')
            );
            $sheet->getStyle(
                'B'.(string)$currentLine
            )->applyFromArray(array_merge($centerStyle, $boldStyle));

            // title for product unit price
            $sheet->setCellValue(
                'C'.(string)$currentLine,
                $this->translator->trans('excel.association.sales_order.deposit_withdrawal.product_unit_price')
            );
            $sheet->getStyle(
                'C'.(string)$currentLine
            )->applyFromArray(array_merge($centerStyle, $boldStyle));

            // title for product quantity
            $sheet->setCellValue(
                'D'.(string)$currentLine,
                $this->translator->trans('excel.association.sales_order.deposit_withdrawal.product_quantity')
            );
            $sheet->getStyle(
                'D'.(string)$currentLine
            )->applyFromArray(array_merge($centerStyle, $boldStyle));

            // title for product total
            $sheet->setCellValue(
                'E'.(string)$currentLine,
                $this->translator->trans('excel.association.sales_order.deposit_withdrawal.product_total')
            );
            $sheet->getStyle(
                'E'.(string)$currentLine
            )->applyFromArray(array_merge($centerStyle, $boldStyle));

            $currentLine++;

            foreach ($producersTransfer->getGroupedSalesOrderRowsDataForProducerId($producerId) as $product) {

                // Product reference
                $sheet->setCellValue(
                    'A'.(string)$currentLine,
                    $product['product_ref']
                );

                $sheet->setCellValue(
                    'B'.(string)$currentLine,
                    $product['product_name']
                );

                // Product unit price
                $sheet->setCellValue(
                    'C'.(string)$currentLine,
                    $formatter->formatCurrency($product['product_unit_price'],$this->currency)
                );
                $sheet->getStyle(
                    'C'.(string)$currentLine
                )->applyFromArray(array_merge($rightStyle));

                // Product quantity
                $sheet->setCellValue(
                    'D'.(string)$currentLine,
                    $product['product_quantity']
                );
                $sheet->getStyle(
                    'D'.(string)$currentLine
                )->applyFromArray(array_merge($centerStyle));

                // Product total
                $sheet->setCellValue(
                    'E'.(string)$currentLine,
                    $formatter->formatCurrency($product['product_total'],$this->currency)
                );
                $sheet->getStyle(
                    'E'.(string)$currentLine
                )->applyFromArray(array_merge($rightStyle));

                $currentLine++;

            }
            $currentLine++;

            // title for total sales order for producer
            $sheet->setCellValue(
                'A'.(string)$currentLine,
                $this->translator->trans('excel.association.sales_order.deposit_withdrawal.total_sales_order_producer')
            );

            // Total sales order for producer
            $sheet->setCellValue(
                'E'.(string)$currentLine,
                $formatter->formatCurrency($producersTransfer->getTotalForProducerId($producerId),$this->currency)
            );
            $sheet->getStyle(
                'E'.(string)$currentLine
            )->applyFromArray(array_merge($rightStyle));
            $currentLine = $currentLine + 2;

            // title for total sales order branch
            $sheet->setCellValue(
                'A'.(string)$currentLine,
                $this->translator->trans('excel.association.sales_order.deposit_withdrawal.total_sales_order_branch')
            );

            // Total sales order branch
            $sheet->setCellValue(
                'E'.(string)$currentLine,
                $formatter->formatCurrency($producersTransfer->getBranchTotalForProducerId($producerId),$this->currency)
            );
            $sheet->getStyle(
                'E'.(string)$currentLine
            )->applyFromArray(array_merge($rightStyle));
            $currentLine = $currentLine + 2;

            // title for total sales order producer + branch
            $sheet->setCellValue(
                'A'.(string)$currentLine,
                $this->translator->trans('excel.association.sales_order.deposit_withdrawal.total_sales_order')
            );

            // Total sales order producer + branch
            $sheet->setCellValue(
                'E'.(string)$currentLine,
                $formatter->formatCurrency($producersTransfer->getTotal($producerId),$this->currency)
            );
            $sheet->getStyle(
                'E'.(string)$currentLine
            )->applyFromArray(array_merge($boldStyle, $rightStyle));
            $currentLine = $currentLine + 2;

            foreach ($producersTransfer->getGroupedCommissionDataForProducerId($producerId) as  $commission => $total) {

                // commission
                $sheet->setCellValue(
                    'A'.(string)$currentLine,
                    $this->translator->trans('excel.association.sales_order.deposit_withdrawal.commission', array(
                        '%commission%'  => $commission
                    ))
                );

                // Total commission
                $sheet->setCellValue(
                    'E'.(string)$currentLine,
                    $formatter->formatCurrency($producersTransfer->getTotalCommission($commission, $total),$this->currency)
                );
                $sheet->getStyle(
                    'E'.(string)$currentLine
                )->applyFromArray(array_merge($rightStyle));

                $currentLine++;
            }
            $currentLine++;

            // title for total to pay
            $sheet->setCellValue(
                'A'.(string)$currentLine,
                $this->translator->trans('excel.association.sales_order.deposit_withdrawal.total_to_pay')
            );
            $sheet->getStyle(
                'A'.(string)$currentLine
            )->applyFromArray(array_merge($borderStyle, $boldStyle));

            // Total to pay
            $sheet->setCellValue(
                'E'.(string)$currentLine,
                $formatter->formatCurrency($producersTransfer->getTotalToPay($producerId),$this->currency)
            );
            $sheet->getStyle(
                'E'.(string)$currentLine
            )->applyFromArray(array_merge($boldStyle, $rightStyle));
            $endCell = (string)$currentLine;
            $currentLine = $currentLine + 2;

            for($i = $startLine; $i <= $endCell; $i++) {
                for ($j = 0; $j <= 4; $j++) {
                    $letter = $this->getColumnNameForNumber($j);
                    $sheet->getStyle(
                        $letter.$i.''
                    )->applyFromArray(array_merge($borderStyle));
                }
            }

        }
        // set width auto
        $columnID = 'A';
        $lastColumn = $sheet->getHighestColumn();
        do {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
            $columnID++;
        } while ($columnID != $lastColumn);

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