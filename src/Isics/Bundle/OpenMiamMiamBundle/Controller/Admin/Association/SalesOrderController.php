<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Association;

use Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Association\BaseController;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class SalesOrderController extends BaseController
{
    /**
     * @param Association $association
     * @param BranchOccurrence $branchOccurrence
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function secureBranchOccurrence(Association $association, BranchOccurrence $branchOccurrence)
    {
        if ($association->getId() !== $branchOccurrence->getBranch()->getAssociation()->getId()) {
            throw $this->createNotFoundException('Invalid branch occurrence for association');
        }
    }

    /**
     * List sales orders
     *
     * @param Association $association
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function listAction(Association $association)
    {
        $this->secure($association);

        $branches = $association->getBranches();
        if (count($branches) === 0) {
            throw $this->createNotFoundException('No branch for association '.$association->getName());
        }

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.association.sales_order.list_for_branch_occurrence',
            array('id' => $association->getId(), 'branchOccurrenceId' => $branches->first()->getId())
        ));
    }

    /**
     * List sales orders for branch occurrence
     *
     * @ParamConverter("branchOccurrence", class="IsicsOpenMiamMiamBundle:BranchOccurrence", options={"mapping": {"branchOccurrenceId": "id"}})
     *
     * @param Association $association
     * @param BranchOccurrence $branchOccurrence
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function listForBranchOccurrenceAction(Association $association, BranchOccurrence $branchOccurrence)
    {
        $this->secure($association);
        $this->secureBranchOccurrence($association, $branchOccurrence);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\SalesOrder:list.html.twig', array(
            'association' => $association,
            'branchOccurrence' => $branchOccurrence,
            'branchOccurrences' => $this->get('open_miam_miam.branch_occurrence_manager')->getToProcessForAssociation($association),
            'salesOrders' => $this->get('open_miam_miam_association_sales_order_manager')->getForBranchOccurrence($branchOccurrence)
        ));
    }
}
