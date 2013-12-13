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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;

class ManagerController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function indexAction(Request $request) {
        if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            $this->createNotFoundException();
        }

        $superAdmins = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamUserBundle:User')->findByRole('ROLE_SUPER_ADMIN');
        $allAdmins = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamUserBundle:User')->findByRole('ROLE_ADMIN');
        $admins = array();

        foreach($allAdmins as $admin) {
            if(! in_array($admin, $superAdmins )) {
                $admins[] = $admin;
            }
        }

        $userFilter = $this->get('open_miam_miam.model.user_filter');
        $filterForm = $this->createForm(
            $this->get('open_miam_miam.form.type.user_filter'),
            clone $userFilter
        );

        $filterForm->handleRequest($request);
        if ($filterForm->isValid()) {
            $userFilter = $filterForm->getData();
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter(
            $this->getDoctrine()->getRepository('IsicsOpenMiamMiamUserBundle:User')
                ->findByUserFilter($userFilter)
                ->getQuery()
        ));

        $pagerfanta->setMaxPerPage($this->container->getParameter('open_miam_miam.super.pagination.user'));

        try {
            $pagerfanta->setCurrentPage($request->query->get('page', 1));
        } catch(NotValidCurrentPageException $e) {
            throw $this->createNotFoundException();
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Manager:index.html.twig', array(
            'admins'       => $admins,
            'superAdmins'  => $superAdmins,
            'users'        => $pagerfanta,
            'filterForm'   => $filterForm->createView()
        ));
    }

    /**
     * Get ajax results
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAjaxResultsAction(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $term = $request->query->get('term');

            $repository = $this->getDoctrine()
                ->getManager()
                ->getRepository('IsicsOpenMiamMiamUserBundle:User');

            $user_list = $repository->findByFirstnameOrLastname($term);

            $user_fullname = array();
            foreach ($user_list as $user) {
                $user_fullname[] = array(
                    'fullname'  => $user->getFullName(),
                    'id'        => $user->getId()
                );
            }

            return new JsonResponse($user_fullname);
        }
    }

    /**
     * Demote user
     *
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function demoteUserAction(User $user) {
        if ($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            $em = $this->getDoctrine()->getManager();

            $user->setRoles( array('ROLE_USER') );
            $em->flush($user);

            return $this->redirect($this->generateUrl('open_miam_miam.admin.super.manager.index'));
        }
    }

    /**
     * Promote user
     *
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function promoteUserAction(User $user) {
        if ($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            $em = $this->getDoctrine()->getManager();
            $user->setRoles( array('ROLE_ADMIN') );
            $em->flush($user);

            return $this->redirect($this->generateUrl('open_miam_miam.admin.super.manager.index'));
        }
    }

}