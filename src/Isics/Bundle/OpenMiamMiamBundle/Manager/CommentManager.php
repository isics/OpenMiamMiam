<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Manager;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Comment;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\CommentRepository;
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
     * @param User        $consumer
     * @param User        $writer
     * @param Association $association
     *
     * @return Comment
     */
    public function createComment(User $consumer, User $writer, Association $association)
    {
        $comment = new Comment();

        $comment->setUser($consumer);
        $comment->setWriter($writer);
        $comment->setAssociation($association);

        return $comment;
    }

    /**
     * Returns not processed comments written by association $association on consumer $consumer
     *
     * @param Association $association
     * @param User        $consumer
     *
     * @return array
     */
    public function getNotProcessedCommentsForAssociationConsumer(Association $association, User $consumer)
    {
        return $this->commentRepository->createQueryBuilder('c')
            ->where('c.association = :association')
            ->setParameter('association', $association)
            ->andWhere('c.user = :user')
            ->setParameter('user', $consumer)
            ->orderBy('c.createdAt', 'desc')
            ->getQuery()
            ->getResult();
    }
}