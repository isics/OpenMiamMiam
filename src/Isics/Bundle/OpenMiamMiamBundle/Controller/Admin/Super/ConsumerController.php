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
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConsumerController extends baseController
{
    /**
     * List consumers
     *
     * @param Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        $handler = $this->get('open_miam_miam.handler.super_consumer');
        $form    = $handler->createSearchForm();
        $qb      = $handler->generateQueryBuilder();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $handler->applyFormFilters($qb, $data);
        } else {
            $handler->applyDefaultFilters($qb);
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb->getQuery()));
        $pagerfanta->setMaxPerPage($this->container->getParameter('open_miam_miam.super.pagination.consumers'));

        try {
            $pagerfanta->setCurrentPage($request->query->get('page', 1));
        } catch (NotValidCurrentPageException $e) {
            throw $this->createNotFoundException();
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Consumer:list.html.twig', array(
            'consumers' => $pagerfanta,
            'form'      => $form->createView()
        ));
    }

    /**
     * Edit a consumer
     *
     * @ParamConverter("consumer", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"consumerId": "id"}})
     *
     * @param Request $request
     * @param User $consumer
     *
     * @return Response
     */
    public function editAction(Request $request, User $consumer)
    {
        $handler = $this->get('open_miam_miam.handler.super_consumer');
        $form = $handler->createProfileForm($consumer);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($consumer);
                $em->flush();

                return $this->redirect($this->generateUrl('open_miam_miam.admin.super.consumer.list'));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Consumer:edit.html.twig', array(
            'consumer'      => $consumer,
            'form'          => $form->createView()
        ));
    }

    /**
     * Delete a consumer
     *
     * @ParamConverter("consumer", class="IsicsOpenMiamMiamUserBundle:User", options={"mapping": {"consumerId": "id"}})
     *
     * @param User $consumer
     *
     * @return Response
     */
    public function deleteAction(User $consumer)
    {
        $em = $this->getDoctrine()->getManager();

        $consumer->setLocked(true);
        $consumer->setIsNewsletterSubscriber(false);
        $consumer->setIsOrdersOpenNotificationSubscriber(false);
        $em->persist($consumer);
        $em->flush();

        return $this->redirect($this->generateUrl('open_miam_miam.admin.super.consumer.list'));
    }
}