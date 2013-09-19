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
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrderRow;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Isics\Bundle\OpenMiamMiamBundle\Model\Cart;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrderConfirmation;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Class SalesOrderManager
 * Manager for sales order
 */
class SalesOrderManager
{
    /**
     * @var ObjectManager $objectManager
     */
    protected $objectManager;

    /**
     * @var ValidatorInterface $validator
     */
    protected $validator;

    /**
     * @var array $config
     */
    protected $config;


    /**
     * Constructs object
     *
     * @param array $config
     * @param ObjectManager $objectManager
     * @param ValidatorInterface $validator
     */
    public function __construct(array $config, ObjectManager $objectManager, ValidatorInterface $validator)
    {
        $this->objectManager = $objectManager;
        $this->validator = $validator;

        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->config = $resolver->resolve($config);
    }

    /**
     * Set the defaults options
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('ref_prefix', 'ref_pad_length'));
    }

    /**
     * Creates sales order from a cart
     *
     * @param Cart $cart
     * @param BranchOccurrence $branchOccurrence
     * @param User $user
     * @param SalesOrderConfirmation $confirmation
     *
     * @throws \DomainException
     *
     * @return SalesOrder
     */
    public function processSalesOrder(Cart $cart,
                                      BranchOccurrence $branchOccurrence,
                                      User $user,
                                      SalesOrderConfirmation $confirmation = null)
    {
        $order = $this->createFromCart($cart, $branchOccurrence, $user);

        if (null !== $confirmation) {
            $order->setConsumerComment($confirmation->getConsumerComment());
        }

        $errors = $this->validator->validate($order, array('FromCart'));
        if (count($errors) > 0) {
            throw new \DomainException('Invalid order.');
        }

        $this->save($order);

        $cart->clearItems();

        return $order;
    }

    /**
     * Creates sales order from a cart
     *
     * @param Cart $cart
     * @param BranchOccurrence $branchOccurrence
     * @param User $user
     *
     * @return SalesOrder
     */
    public function createFromCart(Cart $cart, BranchOccurrence $branchOccurrence, User $user)
    {
        $order = new SalesOrder();

        $order->setDate(new \DateTime());
        $order->setTotal($cart->getTotal());

        $order->setBranchOccurrence($branchOccurrence);

        $order->setUser($user);
        $order->setFirstname($user->getFirstname());
        $order->setLastname($user->getLastname());
        $order->setAddress1($user->getAddress1());
        $order->setAddress2($user->getAddress2());
        $order->setZipcode($user->getZipcode());
        $order->setCity($user->getCity());

        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();

            $orderRow = new SalesOrderRow();
            $orderRow->setProduct($product);
            $orderRow->setName($product->getName());
            $orderRow->setRef($product->getRef());
            $orderRow->setIsBio($product->getIsBio());
            $orderRow->setUnitPrice($product->getPrice());
            $orderRow->setQuantity($item->getQuantity());
            $orderRow->setTotal($item->getTotal());

            $order->addSalesOrderRow($orderRow);
        }

        return $order;
    }

    /**
     * Saves sales order
     *
     * @param SalesOrder $order
     */
    public function save(SalesOrder $order)
    {
        // Increase reference for order
        $association = $order->getBranchOccurrence()->getBranch()->getAssociation();
        $association->setOrderRefCounter($association->getOrderRefCounter()+1);
        $this->objectManager->persist($association);

        // Sets ref
        $order->setRef(sprintf(
            '%s%s',
            $this->config['ref_prefix'],
            str_pad($association->getOrderRefCounter(), $this->config['ref_pad_length'], '0', STR_PAD_LEFT)
        ));

        // Update product stocks
        foreach ($order->getSalesOrderRows() as $row) {
            $product = $row->getProduct();
            if (null !== $product && $product->getAvailability() == Product::AVAILABILITY_ACCORDING_TO_STOCK) {
                $product->setStock($product->getStock()-$row->getQuantity());
                $this->objectManager->persist($product);
            }
        }

        // Save
        $this->objectManager->persist($order);

        $this->objectManager->flush();
    }
}
