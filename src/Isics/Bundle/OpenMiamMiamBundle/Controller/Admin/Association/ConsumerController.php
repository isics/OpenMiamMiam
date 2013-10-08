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
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Symfony\Component\HttpFoundation\Request;

class ConsumerController extends BaseController
{
    /**
     * List consumers
     *
     * @param Request $request
     * @param Association $association
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function listAction(Request $request, Association $association)
    {
        $this->secure($association);

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter(
            $this->getDoctrine()->getRepository('IsicsOpenMiamMiamUserBundle:User')
                    ->getForAssociationQueryBuilder($association)
                    ->getQuery()
        ));
        $pagerfanta->setMaxPerPage($this->container->getParameter('open_miam_miam.consumers_pagination'));

        try {
            $pagerfanta->setCurrentPage($request->query->get('page', 1));
        } catch(NotValidCurrentPageException $e) {
            throw $this->createNotFoundException();
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Consumer:list.html.twig', array(
            'association'=> $association,
            'consumers' => $pagerfanta
        ));
    }
}
