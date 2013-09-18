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
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\OrderConfirmationType;
use Isics\Bundle\OpenMiamMiamBundle\Model\Cart;
use Isics\Bundle\OpenMiamMiamBundle\Model\OrderConfirmation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends Controller
{
    /**
     * Confirms order
     *
     * @ParamConverter("branch", class="IsicsOpenMiamMiamBundle:Branch", options={"mapping": {"branch_slug": "slug"}})
     *
     * @param Request $request
     * @param Branch $branch Branch
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmAction(Request $request, Branch $branch)
    {
        $cart = $this->get('open_miam_miam.cart_manager')->get($branch);
        if (count($cart->getItems()) == 0) {
            return $this->redirectToCart($cart);
        }

        $form = $this->createForm(
            new OrderConfirmationType(),
            new OrderConfirmation(),
            array(
                'action' => $this->generateUrl(
                    'open_miam_miam_order_confirm',
                    array('branch_slug' => $cart->getBranch()->getSlug())
                ),
                'method' => 'POST'
            )
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                die('TODO');
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Order:confirm.html.twig', array(
            'branch' => $branch,
            'cart'   => $cart,
            'user'   => $this->get('security.context')->getToken()->getUser(),
            'form'   => $form->createView()
        ));
    }

    /**
     * Redirect to cart
     *
     * @param Cart $cart
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToCart(Cart $cart)
    {
        return $this->redirect($this->generateUrl('open_miam_miam_cart_show', array('branch_slug' => $cart->getBranch()->getSlug())));
    }
}
