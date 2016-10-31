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
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\AssociationHasProducerType;
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\AssociationProducerExportTransferType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;


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

        $form = $this->container->get('form.factory')
            ->createNamedBuilder(
                'open_miam_miam_association_producer_export_transfer',
                AssociationProducerExportTransferType::class,
                null,
                array(
                    'action' => $this->generateUrl('open_miam_miam.admin.association.producer.export', array('id' => $association->getId())),
                    'method' => 'POST'
                )
            )
            ->getForm();


        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Producer:list.html.twig', array(
            'form'                    => $form->createView(),
            'branches'                => $association->getBranches(),
            'associationHasProducers' => $associationHasProducers
        ));
    }

    /**
     * Edit producers branch
     *
     * @ParamConverter("association", class="IsicsOpenMiamMiamBundle:Association", options={"mapping": {"associationId": "id"}})
     * @ParamConverter("producer", class="IsicsOpenMiamMiamBundle:Producer", options={"mapping": {"producerId": "id"}})
     *
     * @param Request                $request
     * @param AssociationHasProducer $associationHasProducer
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function editAction(Request $request, AssociationHasProducer $associationHasProducer)
    {
        if (null !== $associationHasProducer->getProducer()->getDeletedAt()) {
            throw $this->createNotFoundException();
        }

        $form = $this->container->get('form.factory')
            ->createNamedBuilder(
                'open_miam_miam_association_has_producer',
                AssociationHasProducerType::class,
                $associationHasProducer
            )
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();

                $this->get('session')->getFlashBag()->add('notice', 'admin.association.producers.message.updated');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.association.producer.list',
                    array('id' => $associationHasProducer->getAssociation()->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Producer:edit.html.twig', array(
            'associationHasProducer' => $associationHasProducer,
            'form'                   => $form->createView(),
        ));
    }

    /**
     * Export association transfert for producer
     *
     * @param Request     $request
     * @param Association $association
     *
     * @return Response
     */
    public function exportAction(Request $request, Association $association)
    {
        $date = $request->get('open_miam_miam_association_producer_export_transfert');
        $monthDate = new \DateTime($date['month']);

        $producerTransfer = $this->get('open_miam_miam.association_manager')
            ->getProducerTransferForMonth($association, $monthDate);

        $document = $this->get('open_miam_miam.association.producer.transfer');

        $filename = $this->get('translator')->trans('excel.association.producer.transfer.filename', array(
            '%year%'  => $monthDate->format('Y'),
            '%month%' => $monthDate->format('m')
        ));

        $response = new StreamedResponse();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment;filename="%s"', $filename));

        $response->setCallback(function() use ($document, $producerTransfer) {
            $document->generate($producerTransfer);

            $writer = new \PHPExcel_Writer_Excel2007($document->getExcel());

            $writer->save('php://output');
        });

        return $response;
    }
}
