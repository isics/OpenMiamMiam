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

class ProductController extends BaseController
{
    /**
     * List products
     *
     * @param Producer $producer
     *
     * @return Response
     */
    public function listAction(Producer $producer)
    {
        $this->secure($producer);

        $products = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Product')->findForProducer($producer);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer\Product:list.html.twig', array(
            'producer' => $producer,
            'products' => $products
        ));
    }
}
