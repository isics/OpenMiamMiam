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
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
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
     * @param Branch      $branch
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
     * Edit branch's calendar
     *
     * @ParamConverter("branch", class="IsicsOpenMiamMiamBundle:Branch", options={"mapping": {"branchId": "id"}})
     *
     * @param Request     $request
     * @param Association $association
     * @param Branch      $branch
     *
     * @return Response
     */
    public function editCalendarAction(Request $request, Association $association, Branch $branch)
    {
        $this->secure($association);
        $this->secureBranch($association, $branch);

        $branchOccurrences = $this->getDoctrine()
            ->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence')
            ->findAllNextForBranch($branch, false, null);

        $branchOccurrencesProducersAttendances = $this->getDoctrine()
            ->getRepository('IsicsOpenMiamMiamBundle:ProducerAttendance')
            ->findForBranchOccurrences($branchOccurrences);

        $branchOccurrencesWithCount = array();

        foreach ($branchOccurrences as $branchOccurrence) {
            $branchOccurrenceWithCount = array();

            $branchOccurrenceWithCount['branchOccurrence'] = $branchOccurrence;
            $branchOccurrenceWithCount['unknownCount'] = 0;
            $branchOccurrenceWithCount['yesCount'] = 0;
            $branchOccurrenceWithCount['noCount'] = 0;

            foreach ($branchOccurrencesProducersAttendances as $branchOccurrencesProducersAttendance) {

                if ($branchOccurrencesProducersAttendance->getBranchOccurrence() == $branchOccurrence) {
                    if ($branchOccurrencesProducersAttendance->getIsAttendee() == true) {
                        $branchOccurrenceWithCount['yesCount']++;
                    } else {
                        $branchOccurrenceWithCount['noCount']++;
                    }
                }
            }


            $branchOccurrenceWithCount['unknownCount'] = count($branchOccurrence->getBranch()->getProducers())
                - $branchOccurrenceWithCount['yesCount'] - $branchOccurrenceWithCount['noCount'];

            $branchOccurrencesWithCount[] = $branchOccurrenceWithCount;
        }

        $branchOccurrenceManager = $this->get('open_miam_miam.branch_occurrence_manager');

        $branchOccurrence = $branchOccurrenceManager->createForBranch($branch);

        $form = $this->createForm(
            $this->get('open_miam_miam.form.type.branch_occurrence'),
            $branchOccurrence,
            array(
                'action' => $this->generateUrl('open_miam_miam.admin.association.branch.edit_calendar', array('id' => $association->getId(), 'branchId' => $branch->getId())),
                'method' => 'POST',
            )
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $branchOccurrenceManager->save($branchOccurrence, $this->get('security.context')->getToken()->getUser());

                $this->get('session')->getFlashBag()->add('notice', 'admin.association.branch.calendar.message.created');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.association.branch.edit_calendar',
                    array('id' => $association->getId(), 'branchId' => $branch->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Branch:editCalendar.html.twig', array(
            'branch'            => $branch,
            'branchOccurrences' => $branchOccurrencesWithCount,
            'form'              => $form->createView(),
            'association'       => $association
        ));
    }

    /**
     * List producers attendances for a branch occurrence
     *
     * @ParamConverter("branchOccurrence", class="IsicsOpenMiamMiamBundle:BranchOccurrence", options={"mapping": {"branchOccurrenceId": "id"}})
     *
     * @param Association $association
     * @param BranchOccurrence $branchOccurrence
     *
     * @return Response
     */
    public function listAttendancesAction(Association $association, BranchOccurrence $branchOccurrence)
    {
        $this->secure($association);
        $this->secureBranch($association, $branchOccurrence->getBranch());

        $producerRepository = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Producer');
        $producersAgree = $producerRepository->filterAttendances($branchOccurrence, true)->getQuery()->getResult();
        $producersDisagree = $producerRepository->filterAttendances($branchOccurrence, false)->getQuery()->getResult();

        $producersNotAnswered = array_diff(
            $producerRepository->filterBranch($branchOccurrence->getBranch())->getQuery()->getResult(),
            $producersAgree,
            $producersDisagree
        );

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Branch:listAttendances.html.twig', array(
            'producersNotAnswered'  => $producersNotAnswered,
            'producersAgree'        => $producersAgree,
            'producersDisagree'     => $producersDisagree
        ));
    }

    /**
     * Delete branch occurrence
     *
     * @ParamConverter("branch", class="IsicsOpenMiamMiamBundle:Branch", options={"mapping": {"branchId": "id"}})
     * @ParamConverter("branchOccurrence", class="IsicsOpenMiamMiamBundle:BranchOccurrence", options={"mapping": {"branchOccurrenceId": "id"}})
     *
     * @param Association      $association
     * @param Branch           $branch
     * @param BranchOccurrence $branchOccurrence
     *
     * @return Response
     */
    public function deleteOccurrenceAction(Association $association, Branch $branch, BranchOccurrence $branchOccurrence)
    {
        $this->secure($association);
        $this->secureBranch($association, $branch);

        $branchOccurrenceManager = $this->get('open_miam_miam.branch_occurrence_manager');
        $branchOccurrenceManager->delete($branchOccurrence);

        $this->get('session')->getFlashBag()->add('notice', 'admin.association.branch.calendar.message.deleted');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.association.branch.edit_calendar',
            array('id' => $association->getId(), 'branchId' => $branch->getId())
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
