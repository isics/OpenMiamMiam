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
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesOrderController extends Controller
{
    /**
     * Show sales order
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $salesOrders = $this->getDoctrine()
            ->getRepository('IsicsOpenMiamMiamBundle:SalesOrder')
            ->findForUser($user);

        return $this->render('IsicsOpenMiamMiamUserBundle:SalesOrder:show.html.twig', array(
            'salesOrders' => $salesOrders,
        ));
    }

    /**
     * Generate order Pdf
     *
     * @param SalesOrder $order
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPdfAction(SalesOrder $order)
    {
        if ($this->get('security.context')->getToken()->getUser() !== $order->getUser()) {
            throw $this->createNotFoundException('Order not found!');
        }

        $salesOrdersPdf = $this->get('open_miam_miam.sales_orders_pdf');
        $salesOrdersPdf->setSalesOrders(array($order));

        $filename = $this->get('translator')->trans(
            'pdf.user.sales_order.filename',
            array('%ref%' => $order->getRef())
        );

        return new StreamedResponse(function() use ($salesOrdersPdf, $filename){
            $salesOrdersPdf->render($filename);
        });
    }
}
