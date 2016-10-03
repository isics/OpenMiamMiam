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

use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Model\Cart\Cart;
use Isics\Bundle\OpenMiamMiamBundle\Model\Cart\CartExpiredException;
use Isics\Bundle\OpenMiamMiamBundle\Model\Cart\CartItem;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
     * @var EntityManager
     */
    protected $entityManager;

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
     * @param BranchOccurrenceManager  $branchOccurrenceManager Branch Occurrence Manager
     * @param EntityManager            $entityManager           Object Manager
     * @param Session                  $session                 Session
     * @param SecurityContextInterface $securityContext         Security context
     */
    public function __construct(BranchOccurrenceManager $branchOccurrenceManager,
                                EntityManager $entityManager,
                                Session $session,
                                SecurityContextInterface $securityContext)
    {
        $this->branchOccurrenceManager = $branchOccurrenceManager;
        $this->entityManager           = $entityManager;
        $this->session                 = $session;
        $this->securityContext         = $securityContext;

        $this->carts = array();
    }

    /**
     * Returns a cart
     *
     * @param Branch $branch Branch
     *
     * @throws \Isics\Bundle\OpenMiamMiamBundle\Model\Cart\CartExpiredException
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

                    $products = $this->entityManager
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
