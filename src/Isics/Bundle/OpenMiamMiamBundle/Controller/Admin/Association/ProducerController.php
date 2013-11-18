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
use Isics\Bundle\OpenMiamMiamBundle\Entity\AssociationHasProducer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


class ProducerController extends BaseController
{
    /**
     * List producers
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

        $associationHasProducers = $this->get('open_miam_miam.association_has_producer_manager')->findForAssociation($association);


        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Producer:list.html.twig', array(
            'branches' =>  $association->getBranches(),
            'associationHasProducers' => $associationHasProducers
        ));
    }

    /**
     * Edit producers branch
     *
     * @ParamConverter("producer", class="IsicsOpenMiamMiamBundle:Producer", options={"mapping": {"producerId": "id"}})
     *
     * @param Request $request
     * @param Association $association
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function editAction(Request $request, AssociationHasProducer $associationHasProducer)
    {
        $form = $this->createForm(
            $this->get('open_miam_miam.form.type.association_has_producer'),
            $associationHasProducer
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.association.producer.list',
                    array('id' => $associationHasProducer->getAssociation()->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Producer:edit.html.twig', array(
            'associationHasProducer' => $associationHasProducer,
            'form' => $form->createView(),
        ));
    }
}
