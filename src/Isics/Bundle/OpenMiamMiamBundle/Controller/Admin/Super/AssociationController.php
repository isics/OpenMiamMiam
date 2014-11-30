<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Super;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Model\Association\AssociationWithOwner;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AssociationController extends Controller
{
    /**
     * List associations
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter(
            $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Association')
                ->createQueryBuilder('p')
                ->addOrderBy('p.name')
                ->getQuery()
        ));
        $pagerfanta->setMaxPerPage($this->container->getParameter('open_miam_miam.super.pagination.association'));

        try {
            $pagerfanta->setCurrentPage($request->query->get('page', 1));
        } catch(NotValidCurrentPageException $e) {
            throw $this->createNotFoundException();
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Association:list.html.twig', array(
            'associations' => $pagerfanta,
        ));
    }

    /**
     * Create Association
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $associationManager = $this->get('open_miam_miam.association_manager');
        $associationWithOwner = $associationManager->getAssociationWithOwner();

        $form = $this->getForm($associationWithOwner);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $associationManager->saveAssociationWithOwner($associationWithOwner, $this->get('security.context')->getToken()->getUser());
                $this->get('session')->getFlashBag()->add('notice', 'admin.super.associations.message.created');

                return $this->redirect($this->generateUrl('open_miam_miam.admin.super.association.list'));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Association:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Edit Association
     *
     * @ParamConverter("association", class="IsicsOpenMiamMiamBundle:Association", options={"mapping": {"associationId": "id"}})
     *
     * @param Request     $request
     * @param Association $association
     *
     * @return Response
     */
    public function editAction(Request $request, Association $association)
    {
        $associationManager = $this->get('open_miam_miam.association_manager');
        $associationWithOwner = $associationManager->getAssociationWithOwner($association);

        $form = $this->getForm($associationWithOwner);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $associationManager->saveAssociationWithOwner($associationWithOwner, $this->get('security.context')->getToken()->getUser());
                $this->get('session')->getFlashBag()->add('notice', 'admin.super.associations.message.updated');

                return $this->redirect($this->generateUrl('open_miam_miam.admin.super.association.list'));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Association:edit.html.twig', array(
            'form'       => $form->createView(),
            'activities' => $associationManager->getActivities($association),
        ));
    }

    /**
     * Return association form
     *
     * @param AssociationWithOwner $associationWithOwner
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function getForm(AssociationWithOwner $associationWithOwner)
    {
        if (null === $associationWithOwner->getAssociation()->getId()) {
            $action = $this->generateUrl(
                'open_miam_miam.admin.super.association.create'
            );
        } else {
            $action = $this->generateUrl(
                'open_miam_miam.admin.super.association.edit',
                array('associationId' => $associationWithOwner->getAssociation()->getId())
            );
        }

        return $this->createForm(
            $this->get('open_miam_miam.form.type.super_association'),
            $associationWithOwner,
            array('action' => $action, 'method' => 'POST')
        );
    }

    /**
     * Delete Association
     *
     * @ParamConverter("association", class="IsicsOpenMiamMiamBundle:Association", options={"mapping": {"associationId": "id"}})
     *
     * @param Association $association
     *
     * @return Response
     */
    public function deleteAction(Association $association)
    {
        $associationManager = $this->get('open_miam_miam.association_manager');
        $associationManager->delete($association, $this->get('security.context')->getToken()->getUser());
        $this->get('session')->getFlashBag()->add('notice', 'admin.super.associations.message.deleted');

        return $this->redirect($this->generateUrl('open_miam_miam.admin.super.association.list'));
    }
}
