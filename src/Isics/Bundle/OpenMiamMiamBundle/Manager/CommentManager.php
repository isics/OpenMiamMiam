<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Manager;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Comment;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\CommentRepository;
use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

class CommentManager
{
    /**
     * @var CommentRepository
     */
    protected $commentRepository;

    /**
     * Constructor
     *
     * @param CommentRepository $commentRepository
     */
    public function __construct(CommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }

    /**
     * Create new comment
     *
     * @param Association $association
     * @param User        $writer
     * @param User        $consumer
     * @param SalesOrder  $salesOrder
     *
     * @return Comment
     */
    public function createComment(Association $association, User $writer, User $consumer = null, SalesOrder $salesOrder = null)
    {
        $comment = new Comment();

        $comment->setAssociation($association);
        $comment->setWriter($writer);

        // If consumer is null, comment refers to anonymous consumer of association
        if (null !== $consumer) {
            $comment->setUser($consumer);
        }

        if (null !== $salesOrder) {
            $comment->setSalesOrder($salesOrder);
        }

        return $comment;
    }

    /**
     * Returns not processed comments written by association $association on consumer $consumer
     *
     * @param Association $association
     * @param User        $consumer
     * @param SalesOrder  $salesOrder
     *
     * @return array
     */
    public function getNotProcessedCommentsForAssociationConsumer(Association $association, User $consumer = null, SalesOrder $salesOrder = null)
    {
        $queryBuilder = $this->commentRepository->createQueryBuilder('c')
            ->where('c.association = :association')
            ->setParameter('association', $association)
            ->andWhere('c.isProcessed = :processed')
            ->setParameter('processed', false)
            ->orderBy('c.createdAt', 'desc');

        if (null !== $consumer) {
            $queryBuilder->andWhere('c.user = :user')
                ->setParameter('user', $consumer);
        } else {
            $queryBuilder->andWhere('c.user IS NULL');
        }

        if (null !== $salesOrder) {
            $queryBuilder
                ->leftJoin('c.salesOrder', 'so')
                ->andWhere($queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('so.id', ':salesOrderId'),
                    $queryBuilder->expr()->isNull('so.id')
                ))
                ->setParameter('salesOrderId', $salesOrder->getId());
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
