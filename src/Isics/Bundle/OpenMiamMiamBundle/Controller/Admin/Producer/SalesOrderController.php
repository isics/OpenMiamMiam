<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Producer;

use Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Producer\BaseController;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;

class SalesOrderController extends BaseController
{
    /**
     * List sales order
     *
     * @param Producer $producer
     *
     * @return Response
     */
    public function listAction(Producer $producer)
    {
        $this->secure($producer);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer\SalesOrder:list.html.twig', array(
            'producer' => $producer,
            'salesOrders' => $this->get('open_miam_miam.producer_sales_order_manager')->getForNextBranchOccurrences($producer)
        ));
    }
}
