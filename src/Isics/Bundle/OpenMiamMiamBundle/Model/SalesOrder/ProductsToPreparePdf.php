<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder;

use Isics\Bundle\OpenMiamMiamBundle\Document\OpenMiamMiamPDF;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class ProductsToPreparePdf
{
    /**
     * @var TCPDF $pdf
     */
    protected $pdf;

    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var ProducerBranchOccurrenceSalesOrders
     */
    protected $producerSalesOrders;

    /**
     * @var array
     */
    protected $productConfig;

    /**
     * @var array $products
     */
    protected $products;



    /**
     * Constructs object
     *
     * @param array $productConfig
     * @param TCPDF $pdf
     * @param EngineInterface $engine
     */
    public function __construct(array $productConfig, \TCPDF $pdf, EngineInterface $engine)
    {
        $this->pdf = $pdf;
        $this->engine = $engine;
        $this->productConfig = $productConfig;
    }

    /**
     * Sets sales orders
     *
     * @param ProducerBranchOccurrenceSalesOrders $producerSalesOrders
     */
    public function setProducerSalesOrders(ProducerBranchOccurrenceSalesOrders $producerSalesOrders)
    {
        $this->producerSalesOrders = $producerSalesOrders;
    }

    /**
     * Builds pdf
     */
    public function build()
    {
        $this->initProducts();

        $this->pdf->AddPage();
        $this->pdf->writeHTML($this->engine->render('IsicsOpenMiamMiamBundle:Pdf:productsToPrepare.html.twig', array(
            'producer'          => $this->producerSalesOrders->getProducer(),
            'products'          => $this->products,
            'branchOccurrence'  => $this->producerSalesOrders->getBranchOccurrence(),
            'sum'               =>$this->producerSalesOrders->getSum(),
        )));
    }

    /**
     * Initialize products from producer sales orders
     *
     * @return array
     */
    public function initProducts()
    {
        $this->products = array();
        foreach ($this->producerSalesOrders->getSalesOrders() as $producerSalesOrder) {
            foreach ($producerSalesOrder->getSalesOrderRows() as $row) {
                if ($row->getRef() == $this->productConfig['artificial_product_ref']) {
                    $this->products[] = array('nb' => $row->getQuantity(), 'total' => $row->getTotal(), 'row' => $row);
                    continue;
                }

                if (!isset($this->products[$row->getRef()])) {
                    $this->products[$row->getRef()] = array('nb' => $row->getQuantity(), 'total' => $row->getTotal(), 'row' => $row);
                } else {
                    $this->products[$row->getRef()]['nb'] += $row->getQuantity();
                    $this->products[$row->getRef()]['total'] += $row->getTotal();
                }
            }
        }

        ksort($this->products);
    }

    /**
     * Returns html
     *
     * @param $filename
     *
     * @return string
     */
    public function render($filename = null)
    {
        $this->build();

        return $this->pdf->Output($filename, 'I');
    }
}
