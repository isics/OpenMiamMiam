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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends BaseController
{
    /**
     * Secures product for producer
     *
     * @param Producer $producer
     * @param Product $product
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function secureProduct(Producer $producer, Product $product)
    {
        if ($producer->getId() !== $product->getProducer()->getId()) {
            throw $this->createNotFoundException('Invalid product for producer');
        }
    }

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
                $productManager->save($product, $this->get('security.context')->getToken()->getUser());

                $this->get('session')->getFlashBag()->add('notice', 'admin.producer.products.message.created');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.producer.product.edit',
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
        $this->secureProduct($producer, $product);

        $productManager = $this->get('open_miam_miam.product_manager');

        $form = $this->getForm($product);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $productManager->save($product, $this->get('security.context')->getToken()->getUser());

                $this->get('session')->getFlashBag()->add('notice', 'admin.producer.products.message.updated');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.producer.product.edit',
                    array('id' => $producer->getId(), 'productId' => $product->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Producer\Product:edit.html.twig', array(
            'producer' => $producer,
            'form' => $form->createView(),
            'activities' => $productManager->getActivities($product)
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
                'open_miam_miam.admin.producer.product.create',
                array('id' => $product->getProducer()->getId())
            );
        } else {
            $action = $this->generateUrl(
                'open_miam_miam.admin.producer.product.edit',
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
        $this->secureProduct($producer, $product);

        $productManager = $this->get('open_miam_miam.product_manager');
        $productManager->delete($product);

        $this->get('session')->getFlashBag()->add('notice', 'admin.producer.products.message.deleted');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.producer.product.list',
            array('id' => $producer->getId())
        ));
    }
}
