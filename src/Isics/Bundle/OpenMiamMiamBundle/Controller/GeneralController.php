<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\BranchRepository;
use Isics\Bundle\OpenMiamMiamBundle\Entity\ProducerAttendance;

class GeneralController extends Controller {
	
	public function showProducerAction($producerSlug)
	{
		$producer = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Producer')->findOneBySlug($producerSlug);
		
		
		if (null === $producer) {
			throw new NotFoundHttpException('Producer not found');
		}
		
		if ($producer->getSlug() !== $producerSlug) {
			return $this->redirect($this->generateUrl('open_miam_miam.producer',
					array(
							'producerSlug'  => $producer->getSlug(),
					)
			), 301);
		}
		$nextAttendancesOf = $this->container->get('open_miam_miam.producer_attendances_manager')->getNextAttendancesOf($producer);

		
		if (null === $nextAttendancesOf) {
			throw new NotFoundHttpException('Attendances not found');
		}

		
		return $this->render('IsicsOpenMiamMiamBundle::showProducer.html.twig', array('producer'  => $producer,'nextAttendancesOf'   => $nextAttendancesOf));
		
	}
	
	
	
}