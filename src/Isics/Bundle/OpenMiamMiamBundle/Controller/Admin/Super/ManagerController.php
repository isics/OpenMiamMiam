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

use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;

class ManagerController extends Controller
{
    /**
     * List managers
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $users = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamUserBundle:User')->findAdmins();

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Manager:list.html.twig', array(
            'users' => $users,
            'form'  => $this->getSearchForm()->createView(),
        ));
    }

    /**
     * Search users not yet admin (AJAX or not)
     *
     * @param Request $request
     *
     * @throws NotFoundHttpException
     */
    public function searchAction(Request $request)
    {
        $keyword = '';

        $form = $this->getSearchForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $keyword = $form->getData()['keyword'];
        }

        // AJAX version
        if ($request->isXmlHttpRequest()) {
            $users = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamUserBundle:User')
                ->getNonAdminsByKeywordQueryBuilder($keyword)
                ->setMaxResults(20)
                ->getQuery()
                ->getResult();

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
            $pagerfanta = new Pagerfanta(new DoctrineORMAdapter(
                $this->getDoctrine()->getRepository('IsicsOpenMiamMiamUserBundle:User')
                    ->getNonAdminsByKeywordQueryBuilder($keyword)
                    ->getQuery()
            ));

            $pagerfanta->setMaxPerPage($this->container->getParameter('open_miam_miam.super.pagination.user'));

            try {
                $pagerfanta->setCurrentPage($request->query->get('page', 1));
            } catch(NotValidCurrentPageException $e) {
                throw $this->createNotFoundException();
            }

            return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Manager:search.html.twig', array(
                'form'  => $form->createView(),
                'users' => $pagerfanta,
            ));
        }
    }

    /**
     * Creates search form
     *
     * @return Form
     */
    protected function getSearchForm()
    {
        return $this->createForm(
            $this->get('open_miam_miam.form.type.search'),
            null,
            array(
                'action' => $this->generateUrl('open_miam_miam.admin.super.manager.search'),
                'method' => 'GET',
            )
        );
    }

    /**
     * Promotes user
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws NotFoundHttpException
     */
    public function promoteAction(User $user) {
        if ($user->hasRole('ROLE_SUPER_ADMIN')) {
            throw $this->createNotFoundException('Do not touch super admin!');
        }

        $userManager = $this->get('open_miam_miam_user.manager.user');
        $userManager->promoteAdmin($user);

        $this->get('session')->getFlashBag()->add('notice', 'admin.super.manager.message.promoted');

        return $this->redirect($this->generateUrl('open_miam_miam.admin.super.manager.list'));
    }

    /**
     * Demotes user
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws NotFoundHttpException
     */
    public function demoteAction(User $user) {
        if ($user->hasRole('ROLE_SUPER_ADMIN')) {
            throw $this->createNotFoundException('Do not touch super admin!');
        }

        $userManager = $this->get('open_miam_miam_user.manager.user');
        $userManager->demoteAdmin($user);

        $this->get('session')->getFlashBag()->add('notice', 'admin.super.manager.message.demoted');

        return $this->redirect($this->generateUrl('open_miam_miam.admin.super.manager.list'));
    }
}