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
use Symfony\Component\HttpFoundation\Request;

class ConsumerController extends BaseController
{
    /**
     * List consumers
     *
     * @param Association $association
     *
     * @return Response
     */
    public function listAction(Association $association)
    {
        $this->secure($association);

        $consumers = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamUserBundle:User')->findForAssociation($association);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Consumer:list.html.twig', array(
            'association'=> $association,
            'consumers' => $consumers
        ));
    }
}
