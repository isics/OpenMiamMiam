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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Model\Producer\ProducerWithOwner;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    public function searchAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')){
            throw new NotFoundHttpException();
        }

        $userRepository = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamUserBundle:User');

        return new JsonResponse(
            $userRepository->filterByKeyword($request->query->get('term'))->getQuery()->getResult()
        );
    }
}