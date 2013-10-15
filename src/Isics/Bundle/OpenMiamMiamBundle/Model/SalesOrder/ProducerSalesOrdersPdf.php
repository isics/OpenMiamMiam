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

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class ProducerSalesOrdersPdf
{
    /**
     * @var \TCPDF $pdf
     */
    protected $pdf;

    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var ProducerBranchOccurrenceSalesOrders
     */
    protected $branchOccurrenceSalesOrders;


    /**
     * Constructs object
     *
     * @param \TCPDF $pdf
     * @param EngineInterface $engine
     */
    public function __construct(\TCPDF $pdf, EngineInterface $engine)
    {
        $this->pdf = $pdf;
        $this->engine = $engine;
    }

    /**
     * Sets producer branch occurrence sales orders
     *
     * @param ProducerBranchOccurrenceSalesOrders $branchOccurrenceSalesOrders
     */
    public function setSalesOrders(ProducerBranchOccurrenceSalesOrders $branchOccurrenceSalesOrders)
    {
        $this->branchOccurrenceSalesOrders = $branchOccurrenceSalesOrders;
    }

    /**
     * Builds pdf
     */
    public function build()
    {
        $this->pdf->AddPage();
        $this->pdf->writeHTML(
            $this->engine->render('IsicsOpenMiamMiamBundle:Pdf:producerSalesOrders.html.twig', array(
                'producer' => $this->branchOccurrenceSalesOrders->getProducer(),
                'branchOccurrenceSalesOrders' => $this->branchOccurrenceSalesOrders,
            ))
        );
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
