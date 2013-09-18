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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\Admin\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * Create product
     *
     * @param Request $request
     * @param Producer $producer
     *
     * @return Response
     */
    public function createAction(Request $request, Producer $producer)
    {
        $this->secure($producer);

        $productManager = $this->get('open_miam_miam.product_manager');
        $product = $productManager->createForProducer($producer);

        $form = $this->getForm($product);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $productManager->save($product);

                $this->get('session')->getFlashBag()->add('notice', 'Product created.');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.producer.edit_product',
                    array('id' => $producer->getId(), 'productId' => $product->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer\Product:create.html.twig', array(
            'producer' => $producer,
            'form' => $form->createView()
        ));
    }

    /**
     * Edit product
     *
     * @ParamConverter("product", class="IsicsOpenMiamMiamBundle:Product", options={"mapping": {"productId": "id"}})
     *
     * @param Request $request
     * @param Producer $producer
     * @param Product $product
     *
     * @return Response
     */
    public function editAction(Request $request, Producer $producer, Product $product)
    {
        $this->secure($producer);

        $form = $this->getForm($product);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $productManager = $this->get('open_miam_miam.product_manager');
                $productManager->save($product);

                $this->get('session')->getFlashBag()->add('notice', 'Product updated.');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.producer.edit_product',
                    array('id' => $producer->getId(), 'productId' => $product->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer\Product:edit.html.twig', array(
            'producer' => $producer,
            'form' => $form->createView()
        ));
    }

    /**
     * Return product form
     *
     * @param Product $product
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function getForm(Product $product)
    {
        if (null === $product->getId()) {
            $action = $this->generateUrl(
                'open_miam_miam.admin.producer.create_product',
                array('id' => $product->getProducer()->getId())
            );
        } else {
            $action = $this->generateUrl(
                'open_miam_miam.admin.producer.edit_product',
                array('id' => $product->getProducer()->getId(), 'productId' => $product->getId())
            );
        }

        return $this->createForm(
            $this->get('open_miam_miam.form.type.product'),
            $product,
            array('action' => $action, 'method' => 'POST')
        );
    }

    /**
     * Delete product
     *
     * @ParamConverter("product", class="IsicsOpenMiamMiamBundle:Product", options={"mapping": {"productId": "id"}})
     *
     * @param Producer $producer
     * @param Product $product
     *
     * @return Response
     */
    public function deleteAction(Producer $producer, Product $product)
    {
        $this->secure($producer);

        $productManager = $this->get('open_miam_miam.product_manager');
        $productManager->delete($product);

        $this->get('session')->getFlashBag()->add('notice', 'Product deleted.');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.producer.list_products',
            array('id' => $producer->getId())
        ));
    }
}
