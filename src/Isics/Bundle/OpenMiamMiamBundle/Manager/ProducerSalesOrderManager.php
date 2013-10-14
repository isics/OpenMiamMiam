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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\ProducerBranchOccurrenceSalesOrders;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\ProducerSalesOrder;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\ProducerSalesOrders;

/**
 * Class ProducerSalesOrderManager
 * Manager for sales order of a producer
 */
class ProducerSalesOrderManager
{
    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * Constructs object
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns sales order of a producer for next branch occurrences
     *
     * @param Producer $producer
     *
     * @return array
     */
    public function getForNextBranchOccurrences(Producer $producer)
    {
        $producerSalesOrders = new ProducerSalesOrders($producer);

        $branchOccurrenceRepository = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence');

        foreach ($producer->getBranches() as $branch) {
            $branchOccurrence = $branchOccurrenceRepository->findOneNextNotClosedForBranch($branch);
            if (null !== $branchOccurrence) {
                $branchOccurrenceSaleOrders = $this->getForBranchOccurrence($producer, $branchOccurrence);
                $producerSalesOrders->addProducerBranchOccurrenceSalesOrders($branchOccurrenceSaleOrders);
            }
        }

        return $producerSalesOrders;
    }

    /**
     * Returns sales order of a producer for a branch occurrence
     *
     * @param Producer $producer
     * @param BranchOccurrence $branchOccurrence
     *
     * @return ProducerBranchOccurrenceSalesOrders
     */
    public function getForBranchOccurrence(Producer $producer, BranchOccurrence $branchOccurrence)
    {
        $salesOrderRepository = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:SalesOrder');

        $branchOccurrenceSaleOrders = new ProducerBranchOccurrenceSalesOrders($producer, $branchOccurrence);
        $orders = $salesOrderRepository->findForProducer($producer, $branchOccurrence);
        foreach ($orders as $order) {
            $branchOccurrenceSaleOrders->addSalesOrder(new ProducerSalesOrder($producer, $order));
        }

        return $branchOccurrenceSaleOrders;
    }

    /**
     * Return activities for producer
     *
     * @param ProducerSalesOrder $order
     */
    public function getActivities(ProducerSalesOrder $order)
    {
        $association = $order->getSalesOrder()->getBranchOccurrence()->getBranch()->getAssociation();

        $qb = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Activity')
                ->findByObjectFromEntityQueryBuilder($order->getSalesOrder());

        return $qb->andWhere(
                    $qb->expr()->orx(
                        $qb->expr()->andx(
                            $qb->expr()->eq('a.targetType', ':targetType'),
                            $qb->expr()->eq('a.targetId', ':targetId')
                        ),
                        $qb->expr()->andx(
                            $qb->expr()->eq('a.targetType', ':targetType2'),
                            $qb->expr()->eq('a.targetId', ':targetId2')
                        )
                    )
                )
                ->setParameter('targetType', 'Isics\Bundle\OpenMiamMiamBundle\Entity\Association')
                ->setParameter('targetId', $association->getId())
                ->setParameter('targetType2', 'Isics\Bundle\OpenMiamMiamBundle\Entity\Producer')
                ->setParameter('targetId2', $order->getProducer())
                ->addOrderBy('a.id', 'DESC')
                ->getQuery()
                ->getResult();
    }
}
