<?php
/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Twig;

use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Workflow\SalesOrderWorkflow;

class SalesOrderWorkflowExtension extends \Twig_Extension
{
    /**
     * @var SalesOrderWorkflow $salesOrderWorkflow
     */
    private $salesOrderWorkflow;

    /**
     * Constructor
     *
     * @param SalesOrderWorkflow $salesOrderWorkflow
     */
    public function __construct(SalesOrderWorkflow $salesOrderWorkflow)
    {
        $this->salesOrderWorkflow = $salesOrderWorkflow;
    }

    /**
     * Returns available functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'can_be_edit' => new \Twig_Function_Method($this, 'canBeEdit')
        );
    }

    /**
     * Returns true if sales order can be edit
     *
     * @param SalesOrder $order
     *
     * @return bool
     */
    public function canBeEdit(SalesOrder $order)
    {
        return $this->salesOrderWorkflow->canBeEdit($order);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'sales_order_workflow_extension';
    }
}
