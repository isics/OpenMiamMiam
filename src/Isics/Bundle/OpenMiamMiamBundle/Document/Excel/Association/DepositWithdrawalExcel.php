<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Document\Excel\Association;

use Isics\Bundle\OpenMiamMiamBundle\Document\Excel\Tools;
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Model\Document\ProducersDepositWithdrawal;
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
     * @var \NumberFormatter
     */
    protected $formatter;

    /**
     * @var \IntlDateFormatter
     */
    protected $intl;

    /**
     * @var array
     */
    protected $styles = array();

    /**
     * @var array $columns Columns mapping
     */
    protected $columns = array(
        'productRef'  => 'A',
        'productName' => 'B',
        'unitPrice'   => 'C',
        'quantity'    => 'D',
        'total'       => 'E',
    );

    /**
     * @var string $firstColumn
     */
    protected $firstColumn = 'A';

    /**
     * @var string $lastColumn
     */
    protected $lastColumn = 'E';

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

        // intl
        $this->formatter = new \NumberFormatter($this->translator->getLocale(), \NumberFormatter::CURRENCY);
        $this->intl = new \IntlDateFormatter($this->translator->getLocale(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'MMMM Y');

        // Styles
        $this->styles['bold'] = array(
            'font' => array(
                'bold' => true,
            )
        );

        $this->styles['center'] = array(
            'alignment'=>array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        );

        $this->styles['vertical_center'] = array(
            'alignment'=>array(
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $this->styles['right'] = array(
            'alignment'=>array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            )
        );

        $this->styles['border'] = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                ),
            )
        );
    }

    /**
     * Build document
     *
     * @param ProducersDepositWithdrawal $model
     * @param BranchOccurrence                   $branchOccurrence
     */
    public function generate(ProducersDepositWithdrawal $producerDepositWithdrawal)
    {
        $currentSheetIndex = 0;

        $this->excel->setActiveSheetIndex($currentSheetIndex);
        $sheet = $this->excel->getActiveSheet();
        $sheet->setTitle($this->translator->trans(
            'excel.association.sales_orders.deposit_withdrawal.summary_title'
        ));
        $sheet->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);

        $currentLine = 1;

        $this->generateSummaryPage($sheet, $currentLine, $producerDepositWithdrawal);

        // Producer parts
        foreach ($producerDepositWithdrawal->getProducers() as  $producerId => $producerName) {
            $sheet = $this->excel->createSheet(++$currentSheetIndex);
            $sheet->setTitle($producerName);
            $currentLine = 1;

            $this->generateProducer(
                $sheet,
                $currentLine,
                $producerDepositWithdrawal,
                $producerId,
                $producerName
            );
        }

        $this->excel->setActiveSheetIndex(0);
    }

    /**
     * Add title in document
     *
     * @param \PHPExcel_Worksheet $sheet       La feuille de calcul
     * @param int                 $line        La ligne courante
     * @param string              $title       Le titre
     * @param string              $firstColumn First column
     * @param string              $lastColumn  Last column
     */
    protected function generateTitle(\PHPExcel_Worksheet $sheet, &$line, $title, $firstColumn = null, $lastColumn = null)
    {
        $firstCell = (null !== $firstColumn ? $firstColumn : $this->firstColumn).$line;
        $lastCell = (null !== $lastColumn ? $lastColumn : $this->lastColumn).$line;

        // Merge cells for document title
        $sheet->mergeCells($firstCell.':'.$lastCell);
        $sheet->setCellValue($firstCell, $title);
        $sheet->getStyle($firstCell)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle($firstCell)->applyFromArray(array_merge(
            $this->styles['center'],
            $this->styles['bold']
        ));
        $sheet->getRowDimension('1')->setRowHeight(50);

        ++$line;
    }

    /**
     * Generate summary page
     *
     * @param \PHPExcel_Worksheet        $sheet                     Data sheet
     * @param int                        $line                      Current line
     * @param ProducersDepositWithdrawal $producerDepositWithdrawal Producer deposit / withdrawal model
     */
    protected function generateSummaryPage(\PHPExcel_Worksheet $sheet,
                                           &$line,
                                           ProducersDepositWithdrawal $producerDepositWithdrawal)
    {
        $branchOccurrence = $producerDepositWithdrawal->getBranchOccurrence();

        // Title
        $this->generateTitle($sheet, $line, implode('', array(
            $branchOccurrence->getBranch()->getName(),
            "\n",
            "\n",
            $branchOccurrence->getEnd()->format('d/m/Y')
        )), 'A', 'F');

        ++$line;

        $startLine = $line;

        $currentColumn = 1;
        $commissionRateData = $producerDepositWithdrawal->getAllCommissionsRate();
        $commissionsRateColumn = array();

        // Table headers
        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn).$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.summary.header.sales_order')
        );
        $sheet->getStyle(Tools::getColumnNameForNumber($currentColumn++).$line)
            ->applyFromArray(array_merge($this->styles['center'], $this->styles['bold']));

        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn).$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.summary.header.branch')
        );
        $sheet->getStyle(Tools::getColumnNameForNumber($currentColumn++).$line)
            ->applyFromArray(array_merge($this->styles['center'], $this->styles['bold']));

        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn).$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.summary.header.total')
        );
        $sheet->getStyle(Tools::getColumnNameForNumber($currentColumn++).$line)
            ->applyFromArray(array_merge($this->styles['center'], $this->styles['bold']));

        foreach ($commissionRateData as $commissionRate => $commissionAmount){
            $commissionsRateColumn[$commissionRate] = $currentColumn;
            $sheet->setCellValue(
                Tools::getColumnNameForNumber($currentColumn).$line,
                $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.summary.header.commission', array(
                    '%commission%' => $commissionRate
                ))
            );
            $sheet->getStyle(Tools::getColumnNameForNumber($currentColumn).$line)
                ->applyFromArray(array_merge($this->styles['center'], $this->styles['bold']));

            ++$currentColumn;
        }
        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn).$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.summary.header.to_pay')
        );
        $sheet->getStyle(Tools::getColumnNameForNumber($currentColumn).$line)
            ->applyFromArray(array_merge($this->styles['center'], $this->styles['bold']));

        $sheet->getRowDimension($line)->setRowHeight(30);
        $sheet->getStyle('A'.$line.':'.Tools::getColumnNameForNumber($currentColumn).$line)
            ->applyFromArray($this->styles['vertical_center']);

        ++$line;

        foreach ($producerDepositWithdrawal->getProducers() as $producerId => $producerName){
            $this->generateSummaryPageProducer(
                $sheet,
                $line,
                $producerDepositWithdrawal,
                $producerId,
                $producerName,
                $commissionsRateColumn
            );
            ++$line;
        }

        $line += 2;
        $currentColumn = 0;

        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn).$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.total')
        );
        $sheet->getStyle(Tools::getColumnNameForNumber($currentColumn++).$line)
            ->applyFromArray($this->styles['bold']);

        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn++).$line,
            $this->formatter->formatCurrency(
                $producerDepositWithdrawal->getSumTotalForProducers($producerId),
                $this->currency
            )
        );

        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn++).$line,
            $this->formatter->formatCurrency(
                $producerDepositWithdrawal->getSumBranchOccurrenceTotalForProducers($producerId),
                $this->currency
            )
        );

        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn++).$line,
            $this->formatter->formatCurrency(
                $producerDepositWithdrawal->getSumTotal($producerId),
                $this->currency
            )
        );

        foreach ($commissionRateData as $commissionRate => $commissionAmount){
            $commissionsRateColumn[$commissionRate] = $currentColumn;
            $sheet->setCellValue(
                Tools::getColumnNameForNumber($currentColumn).$line,
                $this->formatter->formatCurrency(
                    $commissionAmount,
                    $this->currency
                )
            );

            ++$currentColumn;
        }

        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn).$line,
            $this->formatter->formatCurrency(
                $producerDepositWithdrawal->getSumTotalToPay($producerId),
                $this->currency
            )
        );

        $column = 0;

        do {
            $sheet->getColumnDimension(Tools::getColumnNameForNumber($column))->setAutoSize(true);
            ++$column;
        } while ($column != $currentColumn);


        $sheet->getStyle(Tools::getColumnNameForNumber(0).$startLine.':'.Tools::getColumnNameForNumber($currentColumn).$line)
            ->applyFromArray($this->styles['border']);

        $sheet->getStyle(Tools::getColumnNameForNumber(1).($startLine+1).':'.Tools::getColumnNameForNumber($currentColumn).$line)
            ->applyFromArray($this->styles['right']);
    }

    /**
     * @param \PHPExcel_Worksheet        $sheet                     Data sheet
     * @param int                        $line                      Current line
     * @param ProducersDepositWithdrawal $producerDepositWithdrawal Producer deposit / withdrawal model
     * @param int                        $producerId                Producer id
     * @param string                     $producerName              Producer name
     * @param array                      $commissionRateColumns     Commission rate columns
     */
    protected function generateSummaryPageProducer(\PHPExcel_Worksheet $sheet,
                                                   &$line,
                                                   ProducersDepositWithdrawal $producerDepositWithdrawal,
                                                   $producerId,
                                                   $producerName,
                                                   array $commissionRateColumns)
    {
        $currentColumn = 0;

        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn++).$line,
            $producerName
        );
        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn++).$line,
            $this->formatter->formatCurrency(
                $producerDepositWithdrawal->getTotalForProducerId($producerId),
                $this->currency
            )
        );
        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn++).$line,
            $this->formatter->formatCurrency(
                $producerDepositWithdrawal->getBranchOccurrenceTotalForProducerId($producerId),
                $this->currency
            )
        );
        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn++).$line,
            $this->formatter->formatCurrency(
                $producerDepositWithdrawal->getTotal($producerId),
                $this->currency
            )
        );

        foreach ($producerDepositWithdrawal->getGroupedCommissionDataForProducerId($producerId) as $commissionRate => $commissionAmount){
            $column = $commissionRateColumns[$commissionRate];

            $sheet->setCellValue(
                Tools::getColumnNameForNumber($column).$line,
                $this->formatter->formatCurrency(
                    $commissionAmount,
                    $this->currency
                )
            );
        }
        $sheet->setCellValue(
            Tools::getColumnNameForNumber(max($commissionRateColumns)+1).$line,
            $this->formatter->formatCurrency(
                $producerDepositWithdrawal->getTotalToPay($producerId),
                $this->currency
            )
        );
    }

    /**
     * Generate a producer part of the document
     *
     * @param \PHPExcel_Worksheet        $sheet                     Data sheet
     * @param                            $line                      Current line
     * @param ProducersDepositWithdrawal $producerDepositWithdrawal Producer deposit / withdrawal model
     * @param                            $producerId                Producer id
     * @param                            $producerName              Producer name
     */
    protected function generateProducer(\PHPExcel_Worksheet $sheet,
                                        &$line,
                                        ProducersDepositWithdrawal $producerDepositWithdrawal,
                                        $producerId,
                                        $producerName)
    {
        $this->generateTitle($sheet, $line, $producerName);

        ++$line;

        $startLine = $line;

        $this->generateProducerTableHeader($sheet, $line);

        foreach ($producerDepositWithdrawal->getGroupedSalesOrderRowsDataForProducerId($producerId) as $product) {
            $this->generateProducerTableProduct($sheet, $line, $product);
        }

        ++$line;

        $this->generateProducerSalesOrdersTotal($sheet, $line, $producerDepositWithdrawal, $producerId);

        ++$line;

        $this->generateProducerBranchSalesOrdersTotal($sheet, $line, $producerDepositWithdrawal, $producerId);

        ++$line;

        $this->generateProducerTotal($sheet, $line, $producerDepositWithdrawal, $producerId);

        ++$line;

        $commissions = $producerDepositWithdrawal->getGroupedCommissionDataForProducerId($producerId);
        foreach ($commissions as  $commissionRate => $commissionTotal) {
            $this->generateProducerCommission($sheet, $line, $commissionRate, $commissionTotal);
        }

        ++$line;

        $this->generateProducerToPayUp($sheet, $line, $producerDepositWithdrawal, $producerId);

        $endLine = $line;

        for($i = $startLine; $i <= $endLine; $i++) {
            for ($j = $this->firstColumn; $j <= $this->lastColumn; $j++) {
                $sheet->getStyle($j.$i)->applyFromArray(array_merge($this->styles['border']));
            }
        }

        ++$line;

        $sheet->getRowDimension($line)->setRowHeight(20);

        // set width auto
        $columnID = 'A';
        $lastColumn = $sheet->getHighestColumn();
        do {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
            $columnID++;
        } while ($columnID != $lastColumn);
    }

    /**
     * Generate producer table header
     *
     * @param \PHPExcel_Worksheet $sheet        Active sheet
     * @param int                 $line         Current line
     */
    protected function generateProducerTableHeader(\PHPExcel_Worksheet $sheet, &$line)
    {
        // title for product ref
        $sheet->setCellValue(
            $this->columns['productRef'].$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.product_ref')
        );
        $sheet->getStyle(
            $this->columns['productRef'].$line
        )->applyFromArray(array_merge($this->styles['center'], $this->styles['bold']));

        // title for product name
        $sheet->setCellValue(
            $this->columns['productName'].$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.product_name')
        );
        $sheet->getStyle(
            $this->columns['productName'].$line
        )->applyFromArray(array_merge($this->styles['center'], $this->styles['bold']));

        // title for product unit price
        $sheet->setCellValue(
            $this->columns['unitPrice'].$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.product_unit_price')
        );
        $sheet->getStyle(
            $this->columns['unitPrice'].$line
        )->applyFromArray(array_merge($this->styles['center'], $this->styles['bold']));

        // title for product quantity
        $sheet->setCellValue(
            $this->columns['quantity'].$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.product_quantity')
        );
        $sheet->getStyle(
            $this->columns['quantity'].$line
        )->applyFromArray(array_merge($this->styles['center'], $this->styles['bold']));

        // title for product total
        $sheet->setCellValue(
            $this->columns['total'].$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.product_total')
        );
        $sheet->getStyle(
            $this->columns['total'].$line
        )->applyFromArray(array_merge($this->styles['center'], $this->styles['bold']));

        ++$line;
    }

    /**
     * Generate producer table product line
     *
     * @param \PHPExcel_Worksheet $sheet
     * @param int                 $line
     * @param array               $product
     */
    protected function generateProducerTableProduct(\PHPExcel_Worksheet $sheet, &$line, array $product)
    {
        // Product reference
        $sheet->setCellValue(
            $this->columns['productRef'].$line,
            $product['product_ref']
        );

        $sheet->setCellValue(
            $this->columns['productName'].$line,
            $product['product_name']
        );

        // Product unit price
        $sheet->setCellValue(
            $this->columns['unitPrice'].$line,
            $this->formatter->formatCurrency($product['product_unit_price'], $this->currency)
        );
        $sheet->getStyle(
            $this->columns['unitPrice'].$line
        )->applyFromArray($this->styles['right']);

        // Product quantity
        $sheet->setCellValue(
            $this->columns['quantity'].$line,
            $product['product_quantity']
        );
        $sheet->getStyle(
            $this->columns['quantity'].$line
        )->applyFromArray($this->styles['center']);

        // Product total
        $sheet->setCellValue(
            $this->columns['total'].$line,
            $this->formatter->formatCurrency($product['product_total'], $this->currency)
        );
        $sheet->getStyle(
            $this->columns['total'].$line
        )->applyFromArray($this->styles['right']);

        ++$line;
    }

    /**
     * Generate sales order total
     *
     * @param \PHPExcel_Worksheet        $sheet                     Active sheet
     * @param int                        $line                      Current line
     * @param ProducersDepositWithdrawal $producerDepositWithdrawal Model
     * @param int                        $producerId                Producer id
     */
    protected function generateProducerSalesOrdersTotal(\PHPExcel_Worksheet $sheet,
                                                        &$line,
                                                        ProducersDepositWithdrawal $producerDepositWithdrawal,
                                                        $producerId)
    {
        // Title
        $sheet->setCellValue(
            $this->columns['productRef'].$line,
            $this->translator->trans(
                'excel.association.sales_orders.deposit_withdrawal.total_sales_order_producer'
            )
        );

        // Total
        $sheet->setCellValue(
            $this->columns['total'].$line,
            $this->formatter->formatCurrency(
                $producerDepositWithdrawal->getTotalForProducerId($producerId),
                $this->currency
            )
        );

        // Style
        $sheet->getStyle(
            $this->columns['total'].$line
        )->applyFromArray($this->styles['right']);

        ++$line;
    }

    /**
     * Generate producer branch occurrence sales orders total
     *
     * @param \PHPExcel_Worksheet        $sheet                     Active sheet
     * @param int                        $line                      Current line
     * @param ProducersDepositWithdrawal $producerDepositWithdrawal Model
     * @param int                        $producerId                Producer id
     */
    protected function generateProducerBranchSalesOrdersTotal(\PHPExcel_Worksheet $sheet,
                                                              &$line,
                                                              ProducersDepositWithdrawal $producerDepositWithdrawal,
                                                              $producerId)
    {
        // Title
        $sheet->setCellValue(
            $this->columns['productRef'].$line,
            $this->translator->trans(
                'excel.association.sales_orders.deposit_withdrawal.total_sales_order_branch'
            )
        );

        // Total
        $sheet->setCellValue(
            $this->columns['total'].$line,
            $this->formatter->formatCurrency(
                $producerDepositWithdrawal->getBranchOccurrenceTotalForProducerId($producerId),
                $this->currency
            )
        );

        // Style
        $sheet->getStyle(
            $this->columns['total'].$line
        )->applyFromArray($this->styles['right']);

        ++$line;
    }

    /**
     * Generate producer total
     *
     * @param \PHPExcel_Worksheet        $sheet                     Active sheet
     * @param int                        $line                      Current line
     * @param ProducersDepositWithdrawal $producerDepositWithdrawal Model
     * @param int                        $producerId                Producer id
     */
    protected function generateProducerTotal(\PHPExcel_Worksheet $sheet,
                                             &$line,
                                             ProducersDepositWithdrawal $producerDepositWithdrawal,
                                             $producerId)
    {
        // Title
        $sheet->setCellValue(
            $this->columns['productRef'].$line,
            $this->translator->trans(
                'excel.association.sales_orders.deposit_withdrawal.total_sales_order'
            )
        );

        // Total
        $sheet->setCellValue(
            $this->columns['total'].$line,
            $this->formatter->formatCurrency(
                $producerDepositWithdrawal->getTotal($producerId),
                $this->currency
            )
        );
        $sheet->getStyle(
            $this->columns['total'].$line
        )->applyFromArray(array_merge($this->styles['bold'], $this->styles['right']));

        ++$line;
    }

    /**
     * Generate producer commission line
     *
     * @param \PHPExcel_Worksheet $sheet                     Active sheet
     * @param int                 $line                      Current line
     * @param float               $commissionRate            Commission rate
     * @param float               $commissionTotal           Commission total
     */
    protected function generateProducerCommission(\PHPExcel_Worksheet $sheet,
                                                  &$line,
                                                  $commissionRate,
                                                  $commissionTotal)
    {
        // Rate
        $sheet->setCellValue(
            $this->columns['productRef'].$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.commission', array(
                '%commission%'  => $commissionRate
            ))
        );

        // Total
        $sheet->setCellValue(
            $this->columns['total'].$line,
            $this->formatter->formatCurrency($commissionTotal, $this->currency)
        );

        // Style
        $sheet->getStyle(
            $this->columns['total'].$line
        )->applyFromArray($this->styles['right']);

        ++$line;
    }

    /**
     * Generate producer to pay up
     *
     * @param \PHPExcel_Worksheet        $sheet                     Active sheet
     * @param int                        $line                      Current line
     * @param ProducersDepositWithdrawal $producerDepositWithdrawal Model
     * @param int                        $producerId                Producer id
     */
    protected function generateProducerToPayUp(\PHPExcel_Worksheet $sheet,
                                               &$line,
                                               ProducersDepositWithdrawal $producerDepositWithdrawal,
                                               $producerId)
    {
        // Title
        $sheet->setCellValue(
            $this->columns['productRef'].$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.total_to_pay')
        );

        // Style
        $sheet->getStyle(
            $this->columns['productRef'].$line
        )->applyFromArray(array_merge($this->styles['border'], $this->styles['bold']));

        // Total
        $sheet->setCellValue(
            $this->columns['total'].$line,
            $this->formatter->formatCurrency(
                $producerDepositWithdrawal->getTotalToPay($producerId),
                $this->currency
            )
        );
        // Style
        $sheet->getStyle(
            $this->columns['total'].$line
        )->applyFromArray(array_merge($this->styles['bold'], $this->styles['right']));
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