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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
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
     * @param Producer $producer
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Producer $producer)
    {
        $this->secure($producer, true);

        $users = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamUserBundle:User')->findManager($producer);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer\Manager:list.html.twig', array(
            'producer' => $producer,
            'users'    => $users,
            'form'     => $this->getSearchForm($producer)->createView(),
        ));
    }

    /**
     * Search users not yet admin (AJAX or not)
     *
     * @param Producer $producer
     * @param Request  $request
     *
     * @throws NotFoundHttpException
     */
    public function searchAction(Producer $producer, Request $request)
    {
        $this->secure($producer, true);

        $keyword = '';

        $form = $this->getSearchForm($producer);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $keyword = $form->getData()['keyword'];
        }

        // AJAX version
        if ($request->isXmlHttpRequest()) {
            $users = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamUserBundle:User')
                ->findNotManagerByKeyword($producer, $keyword, 0, 20);

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
                function () use ($repository, $producer, $keyword) {
                    return $repository->countNotManagerByKeyword(
                        $producer,
                        $keyword
                    );
                },
                function ($offset, $length) use ($repository, $producer, $keyword) {
                    return $repository->findNotManagerByKeyword(
                        $producer,
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

            return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer\Manager:search.html.twig', array(
                'producer' => $producer,
                'form'     => $form->createView(),
                'users'    => $pagerfanta,
            ));
        }
    }

    /**
     * Creates search form
     *
     * @param Producer $producer
     *
     * @return Form
     */
    protected function getSearchForm(Producer $producer)
    {
        return $this->createForm(
            $this->get('open_miam_miam.form.type.search'),
            null,
            array(
                'action' => $this->generateUrl('open_miam_miam.admin.producer.manager.search', array('id' => $producer->getId())),
                'method' => 'GET',
         )
     );
    }

    /**
     * Promotes user
     *
     * @ParamConverter("user", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"userId": "id"}})
     *
     * @param Producer $producer
     * @param User     $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws NotFoundHttpException
     */
    public function promoteAction(Producer $producer, User $user)
    {
        $this->secure($producer, true);

        try {
            $userManager = $this->get('open_miam_miam_user.manager.user');
            $userManager->promoteOperator($producer, $user);
        } catch (\RuntimeException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        $this->get('session')->getFlashBag()->add('notice', 'admin.producer.manager.message.promoted');

        return $this->redirect($this->generateUrl('open_miam_miam.admin.producer.manager.list', array('id' => $producer->getId())));
    }

    /**
     * Demotes user
     *
     * @ParamConverter("user", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"userId": "id"}})
     *
     * @param Producer $producer
     * @param User      $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws NotFoundHttpException
     */
    public function demoteAction(Producer $producer, User $user)
    {
        $this->secure($producer, true);

        try {
            $userManager = $this->get('open_miam_miam_user.manager.user');
            $userManager->demoteOperator($producer, $user);
        } catch (\RuntimeException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        $this->get('session')->getFlashBag()->add('notice', 'admin.producer.manager.message.demoted');

        return $this->redirect($this->generateUrl('open_miam_miam.admin.producer.manager.list', array('id' => $producer->getId())));
    }
}