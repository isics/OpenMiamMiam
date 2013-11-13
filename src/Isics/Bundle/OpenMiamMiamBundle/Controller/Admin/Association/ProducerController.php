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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Payment;
use Isics\Bundle\OpenMiamMiamBundle\Entity\PaymentAllocation;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

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
        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Producer:list.html.twig', array(
            'branches' => $association->getBranches(),
            'producers' => $association->getProducers()
        ));
    }
}
