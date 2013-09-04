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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\CartItemType;
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\CartType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
    /**
     * Shows cart summary
     *
     * @param Branch $branch Branch
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function summarizeAction(Branch $branch)
    {
        $cart = $this->getCart($branch);

        $branchOccurrenceManager = $this->container->get('open_miam_miam.branch_occurrence_manager');

        return $this->render('IsicsOpenMiamMiamBundle:Cart:summarize.html.twig', array(
            'branch'               => $branch,
            'cart'                 => $cart,
            'nextBranchOccurrence' => $branchOccurrenceManager->getNext($branch),
            'closingDateTime'      => $branchOccurrenceManager->getClosingDateTime($branch),
            'openingDateTime'      => $branchOccurrenceManager->getOpeningDateTime($branch),
        ));
    }

    /**
     * Shows cart details (GET)
     *
     * @ParamConverter("branch", class="IsicsOpenMiamMiamBundle:Branch", options={"mapping": {"branch_slug": "slug"}})
     *
     * @param Branch $branch Branch
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Branch $branch)
    {
        $cart = $this->getCart($branch);

        if ($cart->isClosed()) {
            throw $this->createNotFoundException('Orders are closed!');
        }

        $form = $this->createForm(
            new CartType(),
            $cart,
            array(
                'action' => $this->generateUrl('open_miam_miam_cart_update', array('branch_slug' => $branch->getSlug())),
                'method' => 'PUT',
            )
        );

        return $this->render('IsicsOpenMiamMiamBundle:Cart:show.html.twig', array(
            'branch' => $branch,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Shows add form
     *
     * @param Branch  $branch  Branch
     * @param Product $product Product
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAddFormAction(Branch $branch, Product $product)
    {
        $cart = $this->getCart($branch);

        if ($cart->isClosed()) {
            return new Response();
        }

        $branchOccurrenceManager = $this->container->get('open_miam_miam.branch_occurrence_manager');

        $productAvailability = $branchOccurrenceManager->getProductAvailability($branch, $product);

        $renderParameters = array(
            'product'             => $product,
            'productAvailability' => $productAvailability,
        );

        if ($productAvailability->isAvailable()) {
            $cartItem = $cart->createItem();
            $cartItem->setProduct($product);
            $cartItem->setQuantity(1);

            $form = $this->createForm(
                new CartItemType(),
                $cartItem,
                array(
                    'action'        => $this->generateUrl('open_miam_miam_cart_add', array('branch_slug' => $branch->getSlug())),
                    'method'        => 'POST',
                    'submit_button' => true,
                )
            );

            $renderParameters['form'] = $form->createView();
        }

        return $this->render('IsicsOpenMiamMiamBundle:Cart:showAddForm.html.twig', $renderParameters);
    }

    /**
     * Adds a product to cart (POST)
     *
     * @ParamConverter("branch", class="IsicsOpenMiamMiamBundle:Branch", options={"mapping": {"branch_slug": "slug"}})
     *
     * @param Request $request Request
     * @param Branch  $branch  Branch
     *
     * @throws NotFoundHttpException
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction(Request $request, Branch $branch)
    {
        $cart     = $this->getCart($branch);
        $cartItem = $cart->createItem();

        $form = $this->createForm(new CartItemType(), $cartItem, array('submit_button' => true));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $cart->addItem($form->getData());

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Item has been added to cart.'
            );
        }

        return $this->redirect($this->generateUrl(
            'open_miam_miam_cart_show',
            array('branch_slug' => $branch->getSlug())
        ));
    }

    /**
     * Updates cart (PUT)
     *
     * @ParamConverter("branch", class="IsicsOpenMiamMiamBundle:Branch", options={"mapping": {"branch_slug": "slug"}})
     *
     * @param Request $request Request
     * @param Branch  $branch  Branch
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, Branch $branch)
    {
        $cart        = $this->getCart($branch);
        $updatedCart = clone $cart;

        $form = $this->createForm(new CartType(), $updatedCart, array(
            'action' => $this->generateUrl('open_miam_miam_cart_update', array('branch_slug' => $branch->getSlug())),
            'method' => 'PUT',
        ));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $cart->setItems($updatedCart->getItems());

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Cart has been updated.'
            );

            return $this->redirect($this->generateUrl(
                'open_miam_miam_cart_show',
                array('branch_slug' => $branch->getSlug())
            ));
        }

        return $this->render('IsicsOpenMiamMiamBundle:Cart:show.html.twig', array(
            'branch' => $branch,
            'cart'   => $cart,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Retrieves the branch cart
     *
     * @param Branch $branch
     *
     * @return Cart
     */
    protected function getCart(Branch $branch)
    {
        return $this->container->get('open_miam_miam.cart_manager')->get($branch);
    }
}
