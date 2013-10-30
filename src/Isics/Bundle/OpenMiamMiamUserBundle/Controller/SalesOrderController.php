<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamUserBundle\Controller;

use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SalesOrderController extends Controller
{

    /**
     * Show sales order
     * 
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function showSalesOrderAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $salesOrders = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:SalesOrder')->findSalesOrderForUser($user);
        
        return $this->render('IsicsOpenMiamMiamUserBundle:Profile:showSalesOrder.html.twig', array(
            'salesOrders' => $salesOrders,
            ));
    }
}
