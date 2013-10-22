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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class BranchController extends BaseController
{
    /**
     * Secures branch for association
     *
     * @param Association $association
     * @param Branch      $branch
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function secureBranch(Association $association, Branch $branch)
    {
        if ($association->getId() !== $branch->getAssociation()->getId()) {
            throw $this->createNotFoundException('Invalid branch for association');
        }
    }

    /**
     * List branches
     *
     * @param Association $association
     *
     * @return Response
     */
    public function listAction(Association $association)
    {
        $this->secure($association);

        $branchesWithNbProducers = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Branch')->findForAssociationWithProducersCount($association);

        foreach ($branchesWithNbProducers as &$branchWithNbProducers) {
            $branchWithNbProducers['nextOccurrence'] = $this->getDoctrine()
                ->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence')
                ->findOneNextNotClosedForBranch($branchWithNbProducers[0]);
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Branch:list.html.twig', array(
            'association'                              => $association,
            'branchesWithNbProducersAndNextOccurrence' => $branchesWithNbProducers,
        ));
    }

    /**
     * Create branch
     *
     * @param Request     $request
     * @param Association $association
     *
     * @return Response
     */
    public function createAction(Request $request, Association $association)
    {
        $this->secure($association);

        $branchManager = $this->get('open_miam_miam.branch_manager');
        $branch = $branchManager->createForAssociation($association);

        $form = $this->getForm($branch);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $branchManager->save($branch, $this->get('security.context')->getToken()->getUser());

                $this->get('session')->getFlashBag()->add('notice', 'admin.association.branch.message.created');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.association.branch.edit',
                    array('id' => $association->getId(), 'branchId' => $branch->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Branch:create.html.twig', array(
            'association' => $association,
            'form'        => $form->createView(),
        ));
    }

    /**
     * Edit branch
     *
     * @ParamConverter("branch", class="IsicsOpenMiamMiamBundle:Branch", options={"mapping": {"branchId": "id"}})
     *
     * @param Request     $request
     * @param Association $association
     * @param Branch     $branch
     *
     * @return Response
     */
    public function editAction(Request $request, Association $association, Branch $branch)
    {
        $this->secure($association);
        $this->secureBranch($association, $branch);

        $branchManager = $this->get('open_miam_miam.branch_manager');

        $form = $this->getForm($branch);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $branchManager->save($branch, $this->get('security.context')->getToken()->getUser());

                $this->get('session')->getFlashBag()->add('notice', 'admin.association.branch.message.updated');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.association.branch.edit',
                    array('id' => $association->getId(), 'branchId' => $branch->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Branch:edit.html.twig', array(
            'association' => $association,
            'form'        => $form->createView(),
            'activities'  => $branchManager->getActivities($branch),
        ));
    }

    /**
     * Return branch form
     *
     * @param Branch $branch
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function getForm(Branch $branch)
    {
        if (null === $branch->getId()) {
            $action = $this->generateUrl(
                'open_miam_miam.admin.association.branch.create',
                array('id' => $branch->getAssociation()->getId())
            );
        } else {
            $action = $this->generateUrl(
                'open_miam_miam.admin.association.branch.edit',
                array('id' => $branch->getAssociation()->getId(), 'branchId' => $branch->getId())
            );
        }

        return $this->createForm(
            $this->get('open_miam_miam.form.type.branch'),
            $branch,
            array('action' => $action, 'method' => 'POST')
        );
    }

    // /**
    //  * Delete branch
    //  *
    //  * @ParamConverter("branch", class="IsicsOpenMiamMiamBundle:Branch", options={"mapping": {"branchId": "id"}})
    //  *
    //  * @param Association $association
    //  * @param Branch     $branch
    //  *
    //  * @return Response
    //  */
    // public function deleteAction(Association $association, Branch $branch)
    // {
    //     $this->secure($association);
    //     $this->secureBranch($association, $branch);

    //     $branchManager = $this->get('open_miam_miam.branch_manager');
    //     $branchManager->delete($branch);

    //     $this->get('session')->getFlashBag()->add('notice', 'admin.association.branch.message.deleted');

    //     return $this->redirect($this->generateUrl(
    //         'open_miam_miam.admin.association.branch.list',
    //         array('id' => $association->getId())
    //     ));
    // }
}
