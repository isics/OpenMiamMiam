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

use Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Producer\BaseController;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Symfony\Component\HttpFoundation\Request;

class CalendarController extends BaseController
{
    /**
     * Manages calendar
     *
     * @param Request $request
     * @param Producer $producer
     *
     * @return Response
     */
    public function editAction(Request $request, Producer $producer)
    {
        $this->secure($producer);

        $attendancesManager = $this->get('open_miam_miam.producer_attendances_manager');
        $form = $this->createForm(
            $this->get('open_miam_miam.form.type.producer_attendances'),
            $attendancesManager->getNextAttendancesOf($producer),
            array(
                'action' => $this->generateUrl(
                    'open_miam_miam.admin.producer.calendar.edit',
                    array('id' => $producer->getId())
                ),
                'method' => 'POST'
            )
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $attendancesManager->updateAttendances($form->getData());

                $this->get('session')->getFlashBag()->add('notice', 'admin.producer.calendar.message.updated');

                return $this->redirect($this->generateUrl('open_miam_miam.admin.producer.calendar.edit', array('id' => $producer->getId())));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer\Calendar:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
