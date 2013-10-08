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

class SalesOrdersPdf
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
     * @var string $view
     */
    protected $view;

    /**
     * @var array
     */
    protected $salesOrders;


    /**
     * Constructs object
     *
     * @param string $view
     * @param \TCPDF $pdf
     * @param EngineInterface $engine
     */
    public function __construct($view, \TCPDF $pdf, EngineInterface $engine)
    {
        $this->pdf = $pdf;
        $this->engine = $engine;
        $this->view = $view;
    }

    /**
     * Sets sales orders
     *
     * @param array $salesOrders
     */
    public function setSalesOrders(array $salesOrders)
    {
        $this->salesOrders = $salesOrders;
    }

    /**
     * Builds pdf
     */
    public function build()
    {
        foreach ($this->salesOrders as $salesOrder) {
            $this->pdf->AddPage();
            $this->pdf->writeHTML(
                $this->engine->render($this->view, array('order' => $salesOrder))
            );
        }
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
