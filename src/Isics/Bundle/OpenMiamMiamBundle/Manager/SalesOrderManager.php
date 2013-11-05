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
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrderRow;
use Isics\Bundle\OpenMiamMiamBundle\Model\Product\ArtificialProduct;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Isics\Bundle\OpenMiamMiamBundle\Model\Cart\Cart;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\SalesOrderConfirmation;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SalesOrderManager
 * Manager for sales order
 */
class SalesOrderManager
{
    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * @var array $orderConfig
     */
    protected $orderConfig;

    /**
     * @var ActivityManager $activityManager
     */
    protected $activityManager;

    /**
     * @var \Swift_mailer
     */
    protected $mailer;

    /**
     * @var array $mailerConfig
     */
    protected $mailerConfig;

    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;


    /**
     * Constructs object
     *
     * @param array               $orderConfig
     * @param EntityManager       $entityManager
     * @param ActivityManager     $activityManager
     * @param \Swift_Mailer       $mailer
     * @param array               $mailerConfig
     * @param EngineInterface     $engine
     * @param TranslatorInterface $translator
     */
    public function __construct(array $orderConfig,
                                EntityManager $entityManager,
                                ActivityManager $activityManager,
                                \Swift_Mailer $mailer,
                                array $mailerConfig,
                                EngineInterface $engine,
                                TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->activityManager = $activityManager;
        $this->mailer = $mailer;
        $this->engine = $engine;
        $this->translator = $translator;

        $resolver = new OptionsResolver();
        $this->setOrderConfigResolverDefaultOptions($resolver);
        $this->orderConfig = $resolver->resolve($orderConfig);

        $resolver = new OptionsResolver();
        $this->setMailerConfigResolverDefaultOptions($resolver);
        $this->mailerConfig = $resolver->resolve($mailerConfig);
    }

    /**
     * Set the defaults options
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function setOrderConfigResolverDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('ref_prefix', 'ref_pad_length', 'artificial_product_ref'));
    }

    /**
     * Set the defaults options
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function setMailerConfigResolverDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('sender_name', 'sender_address'));
    }

    /**
     * Returns sales orders for branch occurrence
     *
     * @param BranchOccurrence $branchOccurrence
     *
     * @return array
     */
    public function getForBranchOccurrence(BranchOccurrence $branchOccurrence)
    {
        return $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:SalesOrder')->findForBranchOccurrence($branchOccurrence);
    }

    /**
     * Creates sales order from a cart
     *
     * @param Cart $cart
     * @param BranchOccurrence $branchOccurrence
     * @param User $user
     * @param SalesOrderConfirmation $confirmation
     *
     * @return SalesOrder
     */
    public function processSalesOrderFromCart(Cart $cart,
                                              BranchOccurrence $branchOccurrence,
                                              User $user,
                                              SalesOrderConfirmation $confirmation = null)
    {
        $order = $this->createFromCart($cart, $branchOccurrence, $user);

        if (null !== $confirmation) {
            $order->setConsumerComment($confirmation->getConsumerComment());
        }

        $this->save($order, $order->getBranchOccurrence()->getBranch()->getAssociation(), $user);

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
            $orderRow->setProducer($product->getProducer());
            $orderRow->setName($product->getName());
            $orderRow->setRef($product->getRef());
            $orderRow->setIsBio($product->getIsBio());
            $orderRow->setUnitPrice($product->getPrice());
            $orderRow->setQuantity($item->getQuantity());

            $order->addSalesOrderRow($orderRow);
        }

        return $order;
    }

    /**
     * Saves sales order
     *
     * @param SalesOrder $order
     * @param mixed $context
     * @param User $user
     */
    public function save(SalesOrder $order, $context, User $user = null)
    {
        // Compute order's data (total...)
        $this->compute($order);

        $activitiesStack = array();

        $isNewOrder = null === $order->getId();

        if ($isNewOrder) {
            // Increase reference for order
            $association = $order->getBranchOccurrence()->getBranch()->getAssociation();
            $association->setOrderRefCounter($association->getOrderRefCounter()+1);
            $this->entityManager->persist($association);

            // Sets ref
            $order->setRef(sprintf(
                '%s%s',
                $this->orderConfig['ref_prefix'],
                str_pad($association->getOrderRefCounter(), $this->orderConfig['ref_pad_length'], '0', STR_PAD_LEFT)
            ));

            // Activity
            $activitiesStack[] = array(
                'transKey' => 'activity_stream.sales_order.created',
                'transParams' => array('%ref%' => $order->getRef()),
                'context' => $context
            );

        } else {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            foreach ($order->getSalesOrderRows() as $row) {
                if (null === $row->getId()) {
                    $activitiesStack[] = array(
                        'transKey' => 'activity_stream.sales_order.row.added',
                        'transParams' => array(
                            '%order_ref%' => $order->getRef(),
                            '%ref%' => $row->getRef(),
                            '%name%' => $row->getName()
                        ),
                        'context' => $row->getProducer()
                    );
                    continue;
                }

                $changeSet = $unitOfWork->getEntityChangeSet($row);
                if (!empty($changeSet)) {
                    $transKey = null;
                    if (isset($changeSet['quantity']) && $changeSet['quantity'][0] != $changeSet['quantity'][1]
                            && isset($changeSet['total']) && $changeSet['total'][0] != $changeSet['total'][1]) {
                        $transKey = 'activity_stream.sales_order.row.quantity_total_updated';
                        $transParams = array(
                            '%order_ref%' => $order->getRef(),
                            '%ref%' => $row->getRef(),
                            '%name%' => $row->getName(),
                            '%old_quantity%' => $this->activityManager->formatFloatNumber($changeSet['quantity'][0]),
                            '%quantity%' => $this->activityManager->formatFloatNumber($row->getQuantity()),
                            '%old_total%' => $this->activityManager->formatFloatNumber($changeSet['total'][0]),
                            '%total%' => $this->activityManager->formatFloatNumber($row->getTotal())
                        );
                    } elseif (isset($changeSet['quantity']) && $changeSet['quantity'][0] != $changeSet['quantity'][1]) {
                        $transKey = 'activity_stream.sales_order.row.quantity_updated';
                        $transParams = array(
                            '%order_ref%' => $order->getRef(),
                            '%ref%' => $row->getRef(),
                            '%name%' => $row->getName(),
                            '%old_quantity%' => $this->activityManager->formatFloatNumber($changeSet['quantity'][0]),
                            '%quantity%' => $this->activityManager->formatFloatNumber($row->getQuantity())
                        );
                    } elseif (isset($changeSet['total']) && $changeSet['total'][0] != $changeSet['total'][1]) {
                        $transKey = 'activity_stream.sales_order.row.total_updated';
                        $transParams = array(
                            '%order_ref%' => $order->getRef(),
                            '%ref%' => $row->getRef(),
                            '%name%' => $row->getName(),
                            '%old_total%' => $this->activityManager->formatFloatNumber($changeSet['total'][0]),
                            '%total%' => $this->activityManager->formatFloatNumber($row->getTotal())
                        );
                    }

                    if (null !== $transKey) {
                        $activitiesStack[] = array(
                            'transKey' => $transKey,
                            'transParams' => $transParams,
                            'context' => $row->getProducer()
                        );
                    }
                }
            }
        }

        // Update product stocks
        foreach ($order->getSalesOrderRows() as $row) {
            $product = $row->getProduct();
            if (null !== $product && $product->getAvailability() == Product::AVAILABILITY_ACCORDING_TO_STOCK) {
                $product->setStock(($product->getStock()-($row->getQuantity()-$row->getOldQuantity())));
                $this->entityManager->persist($product);
            }
        }

        // Save
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        // Send mail to consumer
        if ($isNewOrder) {
            $this->sendMailToConsumer($order);
        }

        // Activity
        foreach ($activitiesStack as $activityParams) {
            $activity = $this->activityManager->createFromEntities(
                $activityParams['transKey'],
                $activityParams['transParams'],
                $order,
                $activityParams['context'],
                $user
            );
            $this->entityManager->persist($activity);
        }

        $this->entityManager->flush();
    }

    /**
     * Deletes a row of a sales order
     *
     * @param SalesOrderRow $row
     * @param User $user
     */
    public function deleteSalesOrderRow(SalesOrderRow $row, User $user = null)
    {
        $order = $row->getSalesOrder();
        $order->removeSalesOrderRow($row);

        $this->compute($order);

        // Update product stocks
        $product = $row->getProduct();
        if (null !== $product && $product->getAvailability() == Product::AVAILABILITY_ACCORDING_TO_STOCK) {
            $product->setStock($product->getStock()+$row->getQuantity());
            $this->entityManager->persist($product);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        // Activity
        $activity = $this->activityManager->createFromEntities(
            'activity_stream.sales_order.row.deleted',
            array('%order_ref%' => $order->getRef(), '%name%' => $row->getName(), '%ref%' => $row->getRef()),
            $order,
            $row->getProducer(),
            $user
        );
        $this->entityManager->persist($activity);
        $this->entityManager->flush();
    }

    /**
     * Adds rows
     *
     * @param SalesOrder $order
     * @param array $products
     * @param ArtificialProduct $artificialProduct
     */
    public function addRows(SalesOrder $order, array $products, ArtificialProduct $artificialProduct)
    {
        if (null !== $artificialProduct->getName() && null !== $artificialProduct->getPrice()) {
            $salesOrderRow = new SalesOrderRow();
            $salesOrderRow->setProducer($artificialProduct->getProducer());
            $salesOrderRow->setName($artificialProduct->getName());
            $salesOrderRow->setRef($artificialProduct->getRef());
            $salesOrderRow->setIsBio(false);

            $salesOrderRow->setUnitPrice($artificialProduct->getPrice());
            $salesOrderRow->setQuantity(1);

            $order->addSalesOrderRow($salesOrderRow);
        }

        foreach ($products as $product) {
            $hasRow = false;
            foreach ($order->getSalesOrderRows() as $row) {
                if (null !== $row->getProduct() && $row->getProduct()->getId() == $product->getId()) {
                    $row->setQuantity($row->getQuantity()+1);
                    $hasRow = true;
                }
            }

            if (!$hasRow) {
                $salesOrderRow = new SalesOrderRow();
                $salesOrderRow->setProduct($product);
                $salesOrderRow->setProducer($product->getProducer());
                $salesOrderRow->setName($product->getName());
                $salesOrderRow->setRef($product->getRef());
                $salesOrderRow->setIsBio($product->getIsBio());
                $salesOrderRow->setUnitPrice($product->getPrice());
                $salesOrderRow->setQuantity(1);

                $order->addSalesOrderRow($salesOrderRow);
            }
        }
    }

    /**
     * Computes data of sales order
     *
     * @param SalesOrder $order
     */
    public function compute(SalesOrder $order)
    {
        // Total
        $total = 0;
        foreach ($order->getSalesOrderRows() as $row) {
            $this->computeSalesOrderRow($row);
            $total += $row->getTotal();
        }
        $order->setTotal($total);

        // Credit
        $credit = -1*$order->getTotal();
        foreach ($order->getPaymentAllocations() as $allocation) {
            $credit += $allocation->getAmount();
        }
        $order->setCredit($credit);
    }

    /**
     * @param SalesOrderRow $row
     */
    public function computeSalesOrderRow(SalesOrderRow $row)
    {
        if (null !== $row->getUnitPrice()) {
            $row->setTotal($row->getQuantity()*$row->getUnitPrice());
        }
    }

    /**
     * Return activities for order
     *
     * @param SalesOrder $order
     */
    public function getActivities(SalesOrder $order)
    {
        return $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Activity')->findByEntities($order);
    }

    /**
     * Send email ton consumer
     *
     * @param SalesOrder $order
     */
    public function sendMailToConsumer(SalesOrder $order)
    {
        if (null === $order->getUser()) {
            return;
        }

        $body = $this->engine->render('IsicsOpenMiamMiamBundle:Mail:consumerNewSalesOrder.html.twig', array('order' => $order));

        $message = \Swift_Message::newInstance()
            ->setFrom(array($this->mailerConfig['sender_address'] => $this->mailerConfig['sender_name']))
            ->setTo($order->getUser()->getEmail())
            ->setSubject($this->translator->trans('mail.consumer.new_order.subject', array('%ref%' => $order->getRef())))
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
    }
}
