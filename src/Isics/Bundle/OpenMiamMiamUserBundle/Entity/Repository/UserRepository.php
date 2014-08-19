<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamUserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\UserFilter;

class UserRepository extends EntityRepository
{
    /**
     * Returns query builder to find users with ROLE_ADMIN or ROLE_SUPER_ADMIN role
     *
     * @return QueryBuilder
     */
    public function getAdminsQueryBuilder()
    {
        return $this->filterAdmins()
            ->orderBy('u.lastname', 'ASC')
            ->AddOrderBy('u.firstname', 'ASC');
    }

    /**
     * Returns consumers for association
     *
     * @param Association $association
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function getForAssociationQueryBuilder(Association $association, QueryBuilder $qb = null)
    {
        if (null === $qb) {
            $qb = $this->createQueryBuilder('u')
                ->where('u.locked = 0');
        }

        return $qb->innerJoin('u.subscriptions', 's')
                ->andWhere('s.association = :association')
                ->setParameter('association', $association)
                ->addOrderBy('u.id')
                ->addGroupBy('u.id');
    }

    /**
     * Returns query builder to find non admins user filterd with a UserFilter
     *
     * @param string $keyword
     *
     * @return QueryBuilder
     */
    public function getNonAdminsByKeywordQueryBuilder($keyword)
    {
        return $this->filterByKeyword($keyword, $this->filterNonAdmins())
            ->orderBy('u.lastname', 'ASC')
            ->addOrderBy('u.firstname', 'ASC');
    }

    /**
     * Filters admins
     *
     * @param QueryBuilder $qb Query builder
     *
     * @return QueryBuilder
     */
    public function filterAdmins(QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('u') : $qb;

        return $qb->andWhere($qb->expr()->orX(
           $qb->expr()->like('u.roles', $qb->expr()->literal('%ROLE_ADMIN%')),
           $qb->expr()->like('u.roles', $qb->expr()->literal('%ROLE_SUPER_ADMIN%'))
        ));
    }

    /**
     * Filters by keyword
     *
     * @pazam string       $keyword keyword
     * @param QueryBuilder $qb      Query builder
     *
     * @return QueryBuilder
     */
    public function filterByKeyword($keyword, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('u') : $qb;

        return $qb->andWhere($qb->expr()->orX(
           $qb->expr()->like('u.email', ':keyword'),
           $qb->expr()->like('u.firstname', ':keyword'),
           $qb->expr()->like('u.lastname', ':keyword')
        ))->setParameter('keyword', "%$keyword%");
    }

    /**
     * Filters non admins
     *
     * @param QueryBuilder $qb Query builder
     *
     * @return QueryBuilder
     */
    public function filterNonAdmins(QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('u') : $qb;

        return $qb->andWhere($qb->expr()->andX(
           $qb->expr()->not($qb->expr()->like('u.roles', $qb->expr()->literal('%ROLE_ADMIN%'))),
           $qb->expr()->not($qb->expr()->like('u.roles', $qb->expr()->literal('%ROLE_SUPER_ADMIN%')))
        ));
    }

    /**
     * Returns consumers for association
     *
     * @param Association $association
     *
     * @return array
     */
    public function findForAssociation(Association $association)
    {
        return $this->getForAssociationQueryBuilder($association)
                ->getQuery()
                ->getResult();
    }

    /**
     * returns users with ACEs
     *
     * @return array
     */
    public function findWithACE()
    {
        $query = <<<QUERY
            SELECT DISTINCT u.*
            FROM fos_user u INNER JOIN acl_security_identities si ON (si.identifier = CONCAT('%s-', u.username))
            JOIN acl_entries e ON (e.security_identity_id = si.id)
            ORDER BY u.lastname
QUERY;

        $query = sprintf(
            $query,
            addslashes('Isics\Bundle\OpenMiamMiamUserBundle\Entity\User')
        );

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('IsicsOpenMiamMiamUserBundle:User', 'u');

        return $this->getEntityManager()->createNativeQuery($query, $rsm)->getResult();
    }

    /**
     * Returns users managing an Association or a Producer
     *
     * @param mixed $object (Association or Producer)
     *
     * @return array
     */
    public function findManager($object)
    {
        if (!$object instanceof Association && !$object instanceof Producer) {
            throw new \InvalidArgumentException('Object must be an instance of Association or Producer.');
        }

        $query = <<<QUERY
            SELECT DISTINCT u.*
            FROM fos_user u
            JOIN acl_security_identities si ON (si.identifier = CONCAT('%s-', u.username))
            JOIN acl_entries e ON (e.security_identity_id = si.id)
            JOIN acl_object_identities oi ON (oi.id = e.object_identity_id )
            JOIN acl_classes c ON (c.id = oi.class_id)
            WHERE c.class_type = '%s'
            AND oi.object_identifier = %s
            ORDER BY u.lastname
QUERY;

        $query = sprintf(
            $query,
            addslashes('Isics\Bundle\OpenMiamMiamUserBundle\Entity\User'),
            addslashes(get_class($object)),
            $object->getId()
        );

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('IsicsOpenMiamMiamUserBundle:User', 'u');

        return $this->getEntityManager()->createNativeQuery($query, $rsm)->getResult();
    }

    /**
     * Return users managing Producers
     *
     * @param array $producerIds Producer ids
     *
     * @return array
     */
    public function findProducerManagerByProducerIds($producerIds)
    {
        if (!count($producerIds)) {
            return array();
        }

        $query = <<<QUERY
            SELECT DISTINCT u.*
            FROM fos_user u
            JOIN acl_security_identities si ON (si.identifier = CONCAT('%s-', u.username))
            JOIN acl_entries e ON (e.security_identity_id = si.id)
            JOIN acl_object_identities oi ON (oi.id = e.object_identity_id )
            JOIN acl_classes c ON (c.id = oi.class_id)
            WHERE c.class_type = '%s'
            AND oi.object_identifier IN (%s)
            ORDER BY u.lastname
QUERY;

        $query = sprintf(
            $query,
            addslashes('Isics\Bundle\OpenMiamMiamUserBundle\Entity\User'),
            addslashes('Isics\Bundle\OpenMiamMiamBundle\Entity\Producer'),
            implode(',', $producerIds)
        );

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('IsicsOpenMiamMiamUserBundle:User', 'u');

        return $this->getEntityManager()->createNativeQuery($query, $rsm)->getResult();
    }

    /**
     * Counts users not managing an Association or a Producer
     *
     * @param mixed   $object (Association or Producer)
     * @param string  $keyword
     *
     * @return Query
     *
     * @throws InvalidArgumentException
     */
    public function countNotManagerByKeyword($object, $keyword)
    {
        if (!$object instanceof Association && !$object instanceof Producer) {
            throw new \InvalidArgumentException('Object must be an instance of Association or Producer.');
        }

        $query = <<<QUERY
            SELECT COUNT(u.id)
            FROM fos_user u
            WHERE u.id NOT IN
            (
                SELECT u.id
                FROM fos_user u
                JOIN acl_security_identities si ON (si.identifier = CONCAT('%s-', u.username))
                JOIN acl_entries e ON (e.security_identity_id = si.id)
                JOIN acl_object_identities oi ON (oi.id = e.object_identity_id)
                JOIN acl_classes c ON (c.id = oi.class_id)
                WHERE c.class_type = '%s'
                AND oi.object_identifier = %s
            )
            AND (%s)
QUERY;

        $keywordQb = $this->filterByKeyword($keyword);

        $query = sprintf(
            $query,
            addslashes('Isics\Bundle\OpenMiamMiamUserBundle\Entity\User'),
            addslashes(get_class($object)),
            $object->getId(),
            $keywordQb->getDqlPart('where')
        );

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('COUNT(u.id)', 'count');

        $query = $this->getEntityManager()->createNativeQuery($query, $rsm);
        $query->setParameters($keywordQb->getQuery()->getParameters());

        return $query->getSingleScalarResult();
    }

    /**
     * Returns users not managing an Association or a Producer
     *
     * @param mixed   $object (Association or Producer)
     * @param string  $keyword
     * @param integer $offset
     * @param integer $length
     *
     * @return Query
     *
     * @throws InvalidArgumentException
     */
    public function findNotManagerByKeyword($object, $keyword, $offset, $length)
    {
        if (!$object instanceof Association && !$object instanceof Producer) {
            throw new \InvalidArgumentException('Object must be an instance of Association or Producer.');
        }

        $query = <<<QUERY
            SELECT DISTINCT u.*
            FROM fos_user u
            WHERE u.id NOT IN
            (
                SELECT u.id
                FROM fos_user u
                JOIN acl_security_identities si ON (si.identifier = CONCAT('%s-', u.username))
                JOIN acl_entries e ON (e.security_identity_id = si.id)
                JOIN acl_object_identities oi ON (oi.id = e.object_identity_id)
                JOIN acl_classes c ON (c.id = oi.class_id)
                WHERE c.class_type = '%s'
                AND oi.object_identifier = %s
            )
            AND (%s)
            ORDER BY u.lastname
            LIMIT %s, %s
QUERY;

        $keywordQb = $this->filterByKeyword($keyword);

        $query = sprintf(
            $query,
            addslashes('Isics\Bundle\OpenMiamMiamUserBundle\Entity\User'),
            addslashes(get_class($object)),
            $object->getId(),
            $keywordQb->getDqlPart('where'),
            $offset,
            $length
        );

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('IsicsOpenMiamMiamUserBundle:User', 'u');

        $query = $this->getEntityManager()->createNativeQuery($query, $rsm);
        $query->setParameters($keywordQb->getQuery()->getParameters());

        return $query->getResult();
    }

    /**
     * Returns query builder to find consumers of branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     * @param int                                     $lastOrderNbDaysConsideringCustomer
     *
     * @return QueryBuilder
     */
    public function getConsumersForBranchesQueryBuilder($branches, $lastOrderNbDaysConsideringCustomer = null)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.locked = 0');

        $branchesIds = array();
        foreach ($branches as $branch) {
            $branchesIds[] = $branch->getId();
        }

        $qb->join('u.salesOrders', 'so')
            ->join('so.branchOccurrence', 'bo')
            ->join('bo.branch', 'b', Expr\Join::WITH, $qb->expr()->in('b.id', $branchesIds));

        if (null !== $lastOrderNbDaysConsideringCustomer) {
            $now = new \DateTime();
            $begin = new \DateTime("-".$lastOrderNbDaysConsideringCustomer." day");
            $qb->andWhere('so.date > :from')
                ->andWhere('so.date < :to')
                ->setParameter('from', $begin)
                ->setParameter('to', $now);
        }

        return $qb;
    }

    /**
     * Returns query builder to find consumers without branch
     *
     * @return QueryBuilder
     */
    public function getConsumersWithoutBranchQueryBuilder()
    {

        return $this->createQueryBuilder('u')
                    ->where('u.locked = 0')
                    ->leftJoin('u.salesOrders', 'so')
                    ->andWhere('so.id IS NULL');
    }

    /**
     * Returns consumers of branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     * @param int                                     $lastOrderNbDaysConsideringCustomer
     *
     * @return array Consumers
     */
    public function findConsumersForBranches($branches, $lastOrderNbDaysConsideringCustomer = null)
    {

        return $this
                ->getConsumersForBranchesQueryBuilder($branches, $lastOrderNbDaysConsideringCustomer)
                ->getQuery()
                ->getResult();
    }

    /**
     * Returns orders open subscribers for branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     * @param int                                     $lastOrderNbDaysConsideringCustomer
     *
     * @return array Subscribers
     */
    public function findOrdersOpenNotificationSubscribersForBranches($branches, $lastOrderNbDaysConsideringCustomer = null)
    {

        return $this
                ->getConsumersForBranchesQueryBuilder($branches, $lastOrderNbDaysConsideringCustomer)
                ->andWhere('u.isOrdersOpenNotificationSubscriber = true')
                ->getQuery()
                ->getResult();
    }

    /**
     * Returns newsletter subscribers for branches
     *
     * @param \Doctrine\Common\Collections\Collection $branches
     * @param int                                     $lastOrderNbDaysConsideringCustomer
     *
     * @return array Subscribers
     */
    public function findNewsletterSubscribersForBranches($branches, $lastOrderNbDaysConsideringCustomer = null)
    {

        return $this
                ->getConsumersForBranchesQueryBuilder($branches, $lastOrderNbDaysConsideringCustomer)
                ->andWhere('u.isNewsletterSubscriber = true')
                ->getQuery()
                ->getResult();
    }

    /**
     * Returns consumers without branch
     *
     * @return array Consumers
     */
    public function findConsumersWithoutBranch()
    {
        return $this->getConsumersWithoutBranchQueryBuilder()
                    ->getQuery()
                    ->getResult();
    }

    /**
     * Returns newsletter subscribers without branch
     *
     * @return array Subscribers
     */
    public function findNewsletterSubscribersWithoutBranch()
    {
        return $this->getConsumersWithoutBranchQueryBuilder()
                    ->andWhere('u.isNewsletterSubscriber = true')
                    ->getQuery()
                    ->getResult();
    }

    /**
     * Find users with ROLE_ADMIN or ROLE_SUPER_ADMIN role
     *
     * @return array
     */
    public function findAdmins() {
        return $this->getAdminsQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * Filters users by id
     *
     * @param QueryBuilder $qb
     * @param integer $ref
     *
     * @return QueryBuilder
     */
    public function refFilter(QueryBuilder $qb, $ref)
    {
        if ($ref !== null) {
            return $qb
                ->andWhere('u.id = :ref')
                ->setParameter('ref', $ref);
        }

        return $qb;
    }

    /**
     * Filters users by last name
     *
     * @param QueryBuilder $qb
     * @param string $lastName
     *
     * @return QueryBuilder
     */
    public function lastNameFilter(QueryBuilder $qb, $lastName)
    {
        if ($lastName !== null) {
            return $qb
                ->andWhere('u.lastname LIKE :lastName')
                ->setParameter('lastName', '%'.$lastName.'%');
        }

        return $qb;
    }

    /**
     * Filters users by first name
     *
     * @param QueryBuilder $qb
     * @param $firstName
     *
     * @return QueryBuilder
     */
    public function firstNameFilter(QueryBuilder $qb, $firstName)
    {
        if ($firstName !== null) {
            return $qb
                ->andWhere('u.firstname LIKE :firstName')
                ->setParameter('firstName', '%'.$firstName.'%');
        }

        return $qb;
    }

    /**
     * Filters users and returns the users who are not deleted
     *
     * @param QueryBuilder $qb
     * @param boolean      $showDeleted
     *
     * @return QueryBuilder
     */
    public function deletedFilter(QueryBuilder $qb, $showDeleted = false)
    {
        if (false === $showDeleted) {
            $qb->andWhere($qb->expr()->eq('u.locked', 0));
        }

        return $qb;
    }
}
