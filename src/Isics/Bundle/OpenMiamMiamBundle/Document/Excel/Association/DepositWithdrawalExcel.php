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
    protected $numberFormatter;

    /**
     * @var \IntlDateFormatter
     */
    protected $intl;

    /**
     * @var array
     */
    protected $styles = array();

    /**
     * @var array
     */
    protected $producerTabs = array();

    /**
     * @var array $columns Columns mapping
     */
    protected $columns = array(
        'productRef'  => 'A',
        'productName' => 'B',
        'unitPrice'   => 'C',
        'dropped_off' => 'D',
        'quantity'    => 'E',
        'total'       => 'F',
    );

    /**
     * @var string $firstColumn
     */
    protected $firstColumn = 'A';

    /**
     * @var string $lastColumn
     */
    protected $lastColumn = 'F';

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
        $this->numberFormatter = new \NumberFormatter($this->translator->getLocale(), \NumberFormatter::DECIMAL);
        $this->numberFormatter->setAttribute(\NumberFormatter::DECIMAL_ALWAYS_SHOWN, true);
        $this->numberFormatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 2);
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

        // Producer parts
        foreach ($producerDepositWithdrawal->getProducers() as  $producerId => $producerName) {
            $sheet = $this->excel->createSheet(++$currentSheetIndex);
            $currentLine = 1;

            $producerTab = array(
                'producer_name'         => $producerName,
                'tab_name'              => $producerName,
                'first_product_line'    => null,
                'last_product_line'     => null,
                'total_products_line'   => null,
                'misc_line'             => null,
                'total_line'            => null,
                'first_commission_line' => null,
                'last_commission_line'  => null,
                'to_pay_up_line'        => null,
                'commission_lines'      => array()
            );

            $this->generateProducer(
                $sheet,
                $currentLine,
                $producerDepositWithdrawal,
                $producerId,
                $producerName,
                $producerTab
            );

            $this->producerTabs[$producerId] = $producerTab;

            $sheet->setTitle($this->getEscapedTabName($producerId));
        }

        $this->excel->setActiveSheetIndex(0);
        $sheet = $this->excel->getActiveSheet();
        $sheet->setTitle($this->translator->trans(
            'excel.association.sales_orders.deposit_withdrawal.summary_title'
        ));
        $sheet->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);

        $currentLine = 1;

        $this->generateSummaryPage($sheet, $currentLine, $producerDepositWithdrawal);
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
        $sheet->getRowDimension($line)->setRowHeight(50);

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
                    '%commission%' => $this->numberFormatter->format($commissionRate)
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

        $producerFirstLine = $line;

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

        ++$line;
        $currentColumn = 0;

        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn).$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.total')
        );
        $sheet->getStyle(Tools::getColumnNameForNumber($currentColumn).$line)
            ->applyFromArray($this->styles['bold']);
        ++$currentColumn;

        $this->writeCurrencyNumber(
            $sheet,
            Tools::getColumnNameForNumber($currentColumn).$line,
            sprintf('=SUM(%s:%s)',
                Tools::getColumnNameForNumber($currentColumn).$producerFirstLine,
                Tools::getColumnNameForNumber($currentColumn).($line - 1)
            )
        );
        ++$currentColumn;

        $this->writeCurrencyNumber(
            $sheet,
            Tools::getColumnNameForNumber($currentColumn).$line,
            sprintf('=SUM(%s:%s)',
                Tools::getColumnNameForNumber($currentColumn).$producerFirstLine,
                Tools::getColumnNameForNumber($currentColumn).($line - 1)
            )
        );
        ++$currentColumn;

        $this->writeCurrencyNumber(
            $sheet,
            Tools::getColumnNameForNumber($currentColumn).$line,
            sprintf('=SUM(%s:%s)',
                Tools::getColumnNameForNumber($currentColumn).$producerFirstLine,
                Tools::getColumnNameForNumber($currentColumn).($line - 1)
            )
        );
        ++$currentColumn;

        foreach ($commissionRateData as $commissionRate => $commissionAmount){
            $commissionsRateColumn[$commissionRate] = $currentColumn;
            $this->writeCurrencyNumber(
                $sheet,
                Tools::getColumnNameForNumber($currentColumn).$line,
                sprintf('=SUM(%s:%s)',
                    Tools::getColumnNameForNumber($currentColumn).$producerFirstLine,
                    Tools::getColumnNameForNumber($currentColumn).($line - 1)
                )
            );

            ++$currentColumn;
        }

        $this->writeCurrencyNumber(
            $sheet,
            Tools::getColumnNameForNumber($currentColumn).$line,
            sprintf('=SUM(%s:%s)',
                Tools::getColumnNameForNumber($currentColumn).$producerFirstLine,
                Tools::getColumnNameForNumber($currentColumn).($line - 1)
            )
        );

        $columnNumber = $currentColumn;
        $stopLine = $line;

        $line += 2;
        $currentColumn = 0;
        $commandNumberLine = $line;

        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn).$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.number_sales_order')
        );

        ++$currentColumn;

        $commandsStartLine = $startLine + 1;
        $commandsStopLine = $stopLine - 2;

        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn).$line,
            sprintf('=COUNTA(%s:%s)',
                Tools::getColumnNameForNumber(1).$commandsStartLine,
                Tools::getColumnNameForNumber(1).$commandsStopLine
            )
        );

        $sheet->getRowDimension($line)->setRowHeight(30);

        ++$line;
        $currentColumn = 0;

        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn).$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.average_amout')
        );

        ++$currentColumn;

        $sheet->setCellValue(
            Tools::getColumnNameForNumber($currentColumn).$line,
            sprintf('=%s/%s',
                Tools::getColumnNameForNumber(3).$stopLine,
                Tools::getColumnNameForNumber(1).$commandNumberLine
            )
        );

        $sheet->getRowDimension($line)->setRowHeight(30);

        $column = 0;

        do {
            $sheet->getColumnDimension(Tools::getColumnNameForNumber($column))->setAutoSize(true);
            ++$column;
        } while ($column != $columnNumber);


        $sheet->getStyle(Tools::getColumnNameForNumber(0).$startLine.':'.Tools::getColumnNameForNumber($columnNumber).$stopLine)
            ->applyFromArray($this->styles['border']);

        $sheet->getStyle(Tools::getColumnNameForNumber(1).($startLine+1).':'.Tools::getColumnNameForNumber($columnNumber).$stopLine)
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
        $producerTab = $this->producerTabs[$producerId];
        $this->writeCurrencyNumber(
            $sheet,
            Tools::getColumnNameForNumber($currentColumn++).$line,
            sprintf('=\'%s\'!%s',
                $this->getEscapedTabName($producerId),
                $this->columns['total'].$producerTab['total_products_line']
            )
        );
        $this->writeCurrencyNumber(
            $sheet,
            Tools::getColumnNameForNumber($currentColumn++).$line,
            sprintf('=\'%s\'!%s',
                $this->getEscapedTabName($producerId),
                $this->columns['total'].$producerTab['misc_line']
            )
        );
        $this->writeCurrencyNumber(
            $sheet,
            Tools::getColumnNameForNumber($currentColumn++).$line,
            sprintf('=\'%s\'!%s',
                $this->getEscapedTabName($producerId),
                $this->columns['total'].$producerTab['total_line']
            )
        );

        foreach ($producerDepositWithdrawal->getGroupedCommissionDataForProducerId($producerId) as $commissionRate => $commissionAmount){
            $column = $commissionRateColumns[$commissionRate];

            $this->writeCurrencyNumber(
                $sheet,
                Tools::getColumnNameForNumber($column).$line,
                sprintf('=\'%s\'!%s',
                    $this->getEscapedTabName($producerId),
                    $this->columns['total'].$producerTab['commission_lines'][$commissionRate]
                )
            );
        }
        $this->writeCurrencyNumber(
            $sheet,
            Tools::getColumnNameForNumber(max($commissionRateColumns)+1).$line,
            sprintf('=\'%s\'!%s',
                $this->getEscapedTabName($producerId),
                $this->columns['total'].$producerTab['to_pay_up_line']
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
     * @param                            $producerTab               Producer tab data
     */
    protected function generateProducer(\PHPExcel_Worksheet $sheet,
                                        &$line,
                                        ProducersDepositWithdrawal $producerDepositWithdrawal,
                                        $producerId,
                                        $producerName,
                                        &$producerTab)
    {
        $this->generateTitle($sheet, $line, $producerName);

        // Title
        $branchOccurrence = $producerDepositWithdrawal->getBranchOccurrence();
        $this->generateTitle($sheet, $line, implode('', array(
            $branchOccurrence->getBranch()->getName(),
            "\n",
            "\n",
            $branchOccurrence->getEnd()->format('d/m/Y')
        )));

        ++$line;

        $startLine = $line;

        $this->generateProducerTableHeader($sheet, $line);
        ++$line;

        $producerDepositWithdrawal->getGroupedSalesOrderRowsDataForProducerId($producerId);

        $salesOrderRows = $producerDepositWithdrawal->getGroupedSalesOrderRowsDataForProducerId($producerId);

        $producerTab['first_product_line'] = $line;
        $producerTab['last_product_line'] = $line + count($salesOrderRows);

        foreach ($salesOrderRows as $product) {
            $this->generateProducerTableProduct($sheet, $line, $product);
            ++$line;
        }

        $line += 2;

        $this->generateProducerSalesOrdersTotal($sheet, $line, $producerDepositWithdrawal, $producerId, $producerTab);
        $producerTab['total_products_line'] = $line;

        $line += 2;

        $this->generateProducerBranchSalesOrdersTotal($sheet, $line, $producerDepositWithdrawal, $producerId);
        $producerTab['misc_line'] = $line;

        $line += 2;

        $this->generateProducerTotal($sheet, $line, $producerDepositWithdrawal, $producerId, $producerTab);
        $producerTab['total_line'] = $line;

        $line += 2;

        $commissions = $producerDepositWithdrawal->getGroupedCommissionDataForProducerId($producerId);

        $producerTab['first_commission_line'] = $line;
        $producerTab['last_commission_line'] = $line + count($commissions);

        foreach ($commissions as  $commissionRate => $commissionTotal) {
            $this->generateProducerCommission($sheet, $line, $commissionRate, $commissionTotal);
            $producerTab['commission_lines'][$commissionRate] = $line;
            ++$line;
        }

        ++$line;

        $this->generateProducerToPayUp($sheet, $line, $producerDepositWithdrawal, $producerId, $producerTab);
        $producerTab['to_pay_up_line'] = $line;

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

        // title for product quantity (dropped off)
        $sheet->setCellValue(
            $this->columns['dropped_off'].$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.dropped_off_product_quantity')
        );
        $sheet->getStyle(
            $this->columns['dropped_off'].$line
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
    }

    /**
     * Generate producer table product line
     *
     * @param \PHPExcel_Worksheet $sheet
     * @param int                 $line
     * @param array               $product
     */
    protected function generateProducerTableProduct(\PHPExcel_Worksheet $sheet, $line, array $product)
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
        $this->writeCurrencyNumber(
            $sheet,
            $this->columns['unitPrice'].$line,
            $product['product_unit_price']
        );
        $sheet->getStyle(
            $this->columns['unitPrice'].$line
        )->applyFromArray($this->styles['right']);

        // Product quantity (dropped off)
        $sheet->setCellValue(
            $this->columns['dropped_off'].$line,
            $product['product_quantity']
        );
        $sheet->getStyle(
            $this->columns['dropped_off'].$line
        )->applyFromArray($this->styles['center']);

        // Product quantity
        $sheet->setCellValue(
            $this->columns['quantity'].$line,
            $product['product_quantity']
        );
        $sheet->getStyle(
            $this->columns['quantity'].$line
        )->applyFromArray($this->styles['center']);

        // Product total
        $this->writeCurrencyNumber(
            $sheet,
            $this->columns['total'].$line,
            $product['product_total']
        );
        $sheet->getStyle(
            $this->columns['total'].$line
        )->applyFromArray($this->styles['right']);
    }

    /**
     * Generate sales order total
     *
     * @param \PHPExcel_Worksheet        $sheet                     Active sheet
     * @param int                        $line                      Current line
     * @param ProducersDepositWithdrawal $producerDepositWithdrawal Model
     * @param int                        $producerId                Producer id
     * @param array                      $producerTab               Producer tab data
     */
    protected function generateProducerSalesOrdersTotal(\PHPExcel_Worksheet $sheet,
                                                        $line,
                                                        ProducersDepositWithdrawal $producerDepositWithdrawal,
                                                        $producerId,
                                                        array $producerTab)
    {
        // Title
        $sheet->setCellValue(
            $this->columns['productRef'].$line,
            $this->translator->trans(
                'excel.association.sales_orders.deposit_withdrawal.total_sales_order_producer'
            )
        );

        $value = $producerTab['last_product_line'] - $producerTab['first_product_line'] > 0 ?
            sprintf('=SUM(%s:%s)',
                $this->columns['total'].$producerTab['first_product_line'],
                $this->columns['total'].($producerTab['last_product_line'] - 1)
            ) :
            0.0 ;

        // Total
        $this->writeCurrencyNumber(
            $sheet,
            $this->columns['total'].$line,
            $value
        );

        // Style
        $sheet->getStyle(
            $this->columns['total'].$line
        )->applyFromArray($this->styles['right']);
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
                                                              $line,
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
        $this->writeCurrencyNumber(
            $sheet,
            $this->columns['total'].$line,
            $producerDepositWithdrawal->getBranchOccurrenceTotalForProducerId($producerId)
        );

        // Style
        $sheet->getStyle(
            $this->columns['total'].$line
        )->applyFromArray($this->styles['right']);
    }

    /**
     * Generate producer total
     *
     * @param \PHPExcel_Worksheet        $sheet                     Active sheet
     * @param int                        $line                      Current line
     * @param ProducersDepositWithdrawal $producerDepositWithdrawal Model
     * @param int                        $producerId                Producer id
     * @param array                      $producerTab               Producer tab
     */
    protected function generateProducerTotal(\PHPExcel_Worksheet $sheet,
                                             $line,
                                             ProducersDepositWithdrawal $producerDepositWithdrawal,
                                             $producerId,
                                             array $producerTab)
    {
        // Title
        $sheet->setCellValue(
            $this->columns['productRef'].$line,
            $this->translator->trans(
                'excel.association.sales_orders.deposit_withdrawal.total_sales_order'
            )
        );

        $value = sprintf('=%s+%s',
            $this->columns['total'].$producerTab['total_products_line'],
            $this->columns['total'].$producerTab['misc_line']
        );

        // Total
        $this->writeCurrencyNumber(
            $sheet,
            $this->columns['total'].$line,
            $value
        );
        $sheet->getStyle(
            $this->columns['total'].$line
        )->applyFromArray(array_merge($this->styles['bold'], $this->styles['right']));
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
                                                  $line,
                                                  $commissionRate,
                                                  $commissionTotal)
    {
        // Rate
        $sheet->setCellValue(
            $this->columns['productRef'].$line,
            $this->translator->trans('excel.association.sales_orders.deposit_withdrawal.commission', array(
                '%commission%'  => $this->numberFormatter->format($commissionRate)
            ))
        );

        // Total
        $this->writeCurrencyNumber(
            $sheet,
            $this->columns['total'].$line,
            $commissionTotal
        );

        // Style
        $sheet->getStyle(
            $this->columns['total'].$line
        )->applyFromArray($this->styles['right']);
    }

    /**
     * Generate producer to pay up
     *
     * @param \PHPExcel_Worksheet        $sheet                     Active sheet
     * @param int                        $line                      Current line
     * @param ProducersDepositWithdrawal $producerDepositWithdrawal Model
     * @param int                        $producerId                Producer id
     * @param array                      $producerTab               Producer tab
     */
    protected function generateProducerToPayUp(\PHPExcel_Worksheet $sheet,
                                               $line,
                                               ProducersDepositWithdrawal $producerDepositWithdrawal,
                                               $producerId,
                                               array $producerTab)
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

        $commissionFormula = $producerTab['last_commission_line'] - $producerTab['first_commission_line'] > 0 ?
            sprintf('SUM(%s:%s)',
                $this->columns['total'].$producerTab['first_commission_line'],
                $this->columns['total'].($producerTab['last_commission_line'] - 1)
            ) :
            0 ;

        $value = sprintf('=%s-%s',
            $this->columns['total'].$producerTab['total_line'],
            $commissionFormula
        );

        // Total
        $this->writeCurrencyNumber(
            $sheet,
            $this->columns['total'].$line,
            $value
        );
        // Style
        $sheet->getStyle(
            $this->columns['total'].$line
        )->applyFromArray(array_merge($this->styles['bold'], $this->styles['right']));
    }

    /**
     * @param \PHPExcel_Worksheet $sheet Active sheet
     * @param string              $cell  Cell
     * @param string              $value Value
     */
    private function writeCurrencyNumber(\PHPExcel_Worksheet $sheet, $cell, $value)
    {
        $sheet->setCellValue($cell, $value);
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(
            '#,##0.00 €'
        );
    }

    /**
     * Returns escaped tab name for formula
     *
     * @param int $producerId
     *
     * @return string|null
     */
    private function getEscapedTabName($producerId)
    {
        if (array_key_exists($producerId, $this->producerTabs)) {
            $illegal = array('.', '?', '!', '*', '/', '[', ']', '\'', ':');

            return substr(str_replace($illegal, ' ', $this->producerTabs[$producerId]['tab_name']), 0, 31);
        }

        return null;
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