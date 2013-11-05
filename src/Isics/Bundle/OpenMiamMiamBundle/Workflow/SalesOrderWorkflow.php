<?php
/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Workflow;

use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;

/**
 * Class SalesOrderWorkflow
 */
class SalesOrderWorkflow
{
    /**
     * Returns true if sales order can be edit
     *
     * @param SalesOrder $order
     *
     * @return bool
     */
    public function canBeEdit(SalesOrder $order)
    {
        return $order->getLeftToPay() == $order->getTotal();
    }
}
