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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Validator\ViolationMapper\ViolationMapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
    /**
     * Shows cart summary
     *
     * @param Branch  $branch   Branch
     * @param boolean $homepage Are we on homepage?
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function summarizeAction(Branch $branch, $homepage = false)
    {
        $cart = $this->getCart($branch);

        $branchOccurrenceManager = $this->container->get('open_miam_miam.branch_occurrence_manager');

        return $this->render('IsicsOpenMiamMiamBundle:Cart:summarize.html.twig', array(
            'branch'               => $branch,
            'cart'                 => $cart,
            'nextBranchOccurrence' => $branchOccurrenceManager->getNext($branch),
            'closingDateTime'      => $branchOccurrenceManager->getClosingDateTime($branch),
            'openingDateTime'      => $branchOccurrenceManager->getOpeningDateTime($branch),
            'homepage'             => $homepage,
        ));
    }

    /**
     * Shows cart details (GET)
     *
     * @ParamConverter("branch", class="IsicsOpenMiamMiamBundle:Branch", options={"mapping": {"branchSlug": "slug"}})
     *
     * @param Branch $branch Branch
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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
            $this->get('open_miam_miam.form.type.cart'),
            $cart,
            array(
                'action' => $this->generateUrl('open_miam_miam.cart.update', array('branchSlug' => $branch->getSlug())),
                'method' => 'PUT',
            )
        );

        $validator = $this->get('validator');
        $errors = $validator->validate($cart);
        $violationMapper = new ViolationMapper();
        foreach ($errors as $error) {
            $violationMapper->mapViolation($error, $form);
        }

        return $this->render('IsicsOpenMiamMiamBundle:Cart:show.html.twig', array(
            'branch' => $branch,
            'cart'   => $cart,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Shows add form
     *
     * @param Branch  $branch   Branch
     * @param Product $product  Product
     * @param string  $view     The view name
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAddFormAction(Branch $branch, Product $product, $view = null)
    {
        $cart = $this->getCart($branch);

        $renderParameters = array(
            'cart'    => $cart,
            'product' => $product,
        );

        if (!$cart->isClosed()) {
            $branchOccurrenceManager = $this->container->get('open_miam_miam.branch_occurrence_manager');
            $productAvailability     = $branchOccurrenceManager->getProductAvailabilityForNext($branch, $product);
            $renderParameters['productAvailability'] = $productAvailability;

            if ($productAvailability->isAvailable()) {
                $cartItem = $cart->createItem();
                $cartItem->setProduct($product);
                $cartItem->setQuantity(1);

                $form = $this->createForm(
                    $this->get('open_miam_miam.form.type.cart_item'),
                    $cartItem,
                    array(
                        'action'        => $this->generateUrl('open_miam_miam.cart.add', array('branchSlug' => $branch->getSlug())),
                        'method'        => 'POST',
                        'submit_button' => true,
                    )
                );

                $renderParameters['form'] = $form->createView();
            }
        }

        if (null === $view) {
            $view = 'IsicsOpenMiamMiamBundle:Cart:showAddForm.html.twig';
        }

        return $this->render($view, $renderParameters);
    }

    /**
     * Adds a product to cart (POST) AJAX or not
     *
     * @todo Validate global quantity of items
     *
     * @ParamConverter("branch", class="IsicsOpenMiamMiamBundle:Branch", options={"mapping": {"branchSlug": "slug"}})
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

        $form = $this->createForm($this->get('open_miam_miam.form.type.cart_item'), $cartItem, array('submit_button' => true));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $cart->addItem($form->getData());

            if ($request->isXmlHttpRequest()) {
                return $this->render('IsicsOpenMiamMiamBundle:Cart:headerCart.html.twig', array(
                    'branch' => $branch,
                    'cart'   => $cart,
                ));
            } else {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'message.cart.added'
                );
            }
        }

        if ($request->isXmlHttpRequest()) {
            return new Response($this->container->get('translator')->trans('cart.unable_to_add_item'), 400);
        }

        return $this->redirect($this->generateUrl(
            'open_miam_miam.cart.show',
            array('branchSlug' => $branch->getSlug())
        ));
    }

    /**
     * Updates cart (PUT) AJAX or not
     *
     * @ParamConverter("branch", class="IsicsOpenMiamMiamBundle:Branch", options={"mapping": {"branchSlug": "slug"}})
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

        $form = $this->createForm($this->get('open_miam_miam.form.type.cart'), $updatedCart, array(
            'action' => $this->generateUrl('open_miam_miam.cart.update', array('branchSlug' => $branch->getSlug())),
            'method' => 'PUT',
        ));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $cart->setItems($updatedCart->getItems());

            if ($request->isXmlHttpRequest()) {
                return new Response(json_encode(array(
                    'headerCart' => $this->renderView('IsicsOpenMiamMiamBundle:Cart:headerCart.html.twig', array(
                        'branch' => $branch,
                        'cart'   => $cart,
                    )),
                    'cart'       => $this->renderView('IsicsOpenMiamMiamBundle:Cart:cart.html.twig', array(
                        'branch' => $branch,
                        'cart'   => $cart,
                        'form'   => $form->createView(),
                    )),
                )));
            } else {
                if ($form->get('checkout')->isClicked()) {
                    return $this->redirect($this->generateUrl(
                        'open_miam_miam.sales_order.confirm',
                        array('branchSlug' => $branch->getSlug())
                    ));
                }

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'message.cart.updated'
                );

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.cart.show',
                    array('branchSlug' => $branch->getSlug())
                ));
            }
        }

        if ($request->isXmlHttpRequest()) {
            return new Response($this->container->get('translator')->trans('cart.unable_to_update'), 400);
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
