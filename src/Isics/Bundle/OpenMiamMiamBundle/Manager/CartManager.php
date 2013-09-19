<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Exception\CartExpiredException;
use Isics\Bundle\OpenMiamMiamBundle\Model\Cart;
use Isics\Bundle\OpenMiamMiamBundle\Model\CartItem;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Manages cart operations
 */
class CartManager
{
    const SESSION_KEY = 'open_miam_miam.carts';

    /**
     * @var array
     */
    protected $carts;

    /**
     * @var BranchOccurrenceManager
     */
    protected $branchOccurrenceManager;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * Constructor
     *
     * @param BranchOccurrenceManager $branchOccurrenceManager Branch Occurrence Manager
     * @param ObjectManager           $objectManager           Object Manager
     * @param Session                 $session                 Session
     * @param SecurityContext         $securityContext         Security context
     */
    public function __construct(BranchOccurrenceManager $branchOccurrenceManager, ObjectManager $objectManager, Session $session, SecurityContext $securityContext)
    {
        $this->branchOccurrenceManager = $branchOccurrenceManager;
        $this->objectManager           = $objectManager;
        $this->session                 = $session;
        $this->securityContext         = $securityContext;

        $this->carts = array();
    }

    /**
     * Returns a cart
     *
     * @param Branch $branch Branch
     *
     * @throws \Isics\Bundle\OpenMiamMiamBundle\Exception\CartExpiredException
     *
     * @return Cart
     */
    public function get(Branch $branch)
    {
        if (!array_key_exists($branch->getId(), $this->carts)) {
            $cartsInSession = $this->session->get(self::SESSION_KEY, array());

            if (!array_key_exists($branch->getId(), $cartsInSession)) {
                $cart = new Cart();

                $cartsInSession[$branch->getId()] = $cart;
                $this->session->set(self::SESSION_KEY, $cartsInSession);
            } else {
                $cart = $cartsInSession[$branch->getId()];
                $cartItems = $cart->getItems();

                // Populates products (not stored in session)
                // and computes cart
                if (0 < count($cartItems)) {
                    $productIds = array();
                    foreach ($cartItems as $cartItem) {
                        $productIds[] = $cartItem->getProductId();
                    }

                    $products = $this->objectManager
                        ->getRepository('IsicsOpenMiamMiamBundle:Product')
                        ->findById($productIds);

                    foreach ($products as $product) {
                        $cartItems[$product->getId()]->setProduct($product);
                    }

                    $cart->compute();
                }
            }

            $cart->setBranch($branch);

            if (!$this->branchOccurrenceManager->hasNext($branch) || $this->branchOccurrenceManager->isInProgress($branch)) {
                $cart->close();

                if (0 > $cart->getNbItems()) {
                    $cart->clearItems();

                    throw new CartExpiredException();
                }
            }

            $this->carts[$branch->getId()] = $cart;
        }

        return $this->carts[$branch->getId()];
    }
}
