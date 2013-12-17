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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Pagerfanta\Adapter\CallbackAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ManagerController extends BaseController
{
    /**
     * List managers
     *
     * @param Association $association
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Association $association)
    {
        $this->secure($association);

        $users = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamUserBundle:User')->findManagingAssociation($association);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Manager:list.html.twig', array(
            'association' => $association,
            'users'       => $users,
            'form'        => $this->getSearchForm($association)->createView(),
        ));
    }

    /**
     * Search users not yet admin (AJAX or not)
     *
     * @param Association $association
     * @param Request     $request
     *
     * @throws NotFoundHttpException
     */
    public function searchAction(Association $association, Request $request)
    {
        $keyword = '';

        $form = $this->getSearchForm($association);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $keyword = $form->getData()['keyword'];
        }

        // AJAX version
        if ($request->isXmlHttpRequest()) {
            $users = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamUserBundle:User')
                ->findNonManagingAssociationByKeyword($association, $keyword, 0, 20);

            $data = array();
            foreach ($users as $user) {
                $data[] = array(
                    'id'    => $user->getId(),
                    'label' => $user->getFullname(),
                );
            }

            return new JsonResponse($data);

        // Standard version
        } else {
            $repository = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamUserBundle:User');
            $pagerfanta = new Pagerfanta(new CallbackAdapter(
                function () use ($repository, $association, $keyword) {
                    return $repository->countNonManagingAssociationByKeyword(
                        $association,
                        $keyword
                    );
                },
                function ($offset, $length) use ($repository, $association, $keyword) {
                    return $repository->findNonManagingAssociationByKeyword(
                        $association,
                        $keyword,
                        $offset,
                        $length
                    );
                }
            ));

            $pagerfanta->setMaxPerPage($this->container->getParameter('open_miam_miam.super.pagination.user'));

            try {
                $pagerfanta->setCurrentPage($request->query->get('page', 1));
            } catch(NotValidCurrentPageException $e) {
                throw $this->createNotFoundException();
            }

            return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Manager:search.html.twig', array(
                'association' => $association,
                'form'        => $form->createView(),
                'users'       => $pagerfanta,
            ));
        }
    }

    /**
     * Creates search form
     *
     * @param Association $association
     *
     * @return Form
     */
    protected function getSearchForm(Association $association)
    {
        return $this->createForm(
            $this->get('open_miam_miam.form.type.search'),
            null,
            array(
                'action' => $this->generateUrl('open_miam_miam.admin.association.manager.search', array('id' => $association->getId())),
                'method' => 'GET',
            )
        );
    }

    /**
     * Promotes user
     *
     * @ParamConverter("user", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"userId": "id"}})
     *
     * @param Association $association
     * @param User        $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws NotFoundHttpException
     */
    public function promoteAction(Association $association, User $user)
    {
        try {
            $userManager = $this->get('open_miam_miam_user.manager.user');
            $userManager->promoteAssociationOperator($association, $user);
        } catch (\RuntimeException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        $this->get('session')->getFlashBag()->add('notice', 'admin.association.manager.message.promoted');

        return $this->redirect($this->generateUrl('open_miam_miam.admin.association.manager.list', array('id' => $association->getId())));
    }

    /**
     * Demotes user
     *
     * @ParamConverter("user", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"userId": "id"}})
     *
     * @param Association $association
     * @param User        $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws NotFoundHttpException
     */
    public function demoteAction(Association $association, User $user)
    {
        try {
            $userManager = $this->get('open_miam_miam_user.manager.user');
            $userManager->demoteAssociationOperator($association, $user);
        } catch (\RuntimeException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        $this->get('session')->getFlashBag()->add('notice', 'admin.association.manager.message.demoted');

        return $this->redirect($this->generateUrl('open_miam_miam.admin.association.manager.list', array('id' => $association->getId())));
    }
}