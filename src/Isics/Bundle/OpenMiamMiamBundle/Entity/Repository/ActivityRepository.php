<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ActivityRepository extends EntityRepository
{
    /**
     * Returns QueryBuilder to find activities by object from entity
     * @param $object
     * @param QueryBuilder $qb
     *
     * @return \Doctrine\ORM\QueryBuilder|QueryBuilder
     */
    public function findByObjectFromEntityQueryBuilder($object, QueryBuilder $qb = null)
    {
        $propertyAccessor = new PropertyAccessor();

        $qb = null === $qb ? $this->createQueryBuilder('a') : $qb;

        $metadata = $this->getEntityManager()->getClassMetadata(get_class($object));

        $idFieldName = $metadata->getSingleIdentifierFieldName();

        $qb->andWhere('a.objectType = :objectType')
                ->setParameter('objectType', $metadata->getName())
                ->andWhere('a.objectId = :objectId')
                ->setParameter('objectId', $propertyAccessor->getValue($object, $idFieldName));

        return $qb;
    }

    /**
     * Returns QueryBuilder to find activities by target from entity
     * @param $target
     * @param QueryBuilder $qb
     *
     * @return \Doctrine\ORM\QueryBuilder|QueryBuilder
     */
    public function findByTargetFromEntityQueryBuilder($target, QueryBuilder $qb = null)
    {
        $propertyAccessor = new PropertyAccessor();

        $qb = null === $qb ? $this->createQueryBuilder('a') : $qb;

        $metadata = $this->getEntityManager()->getClassMetadata(get_class($target));
        $idFieldName = $metadata->getSingleIdentifierFieldName();

        $qb->andWhere('a.targetType = :targetType')
                ->setParameter('targetType', $metadata->getName())
                ->andWhere('a.targetId = :targetId')
                ->setParameter('targetId', $propertyAccessor->getValue($target, $idFieldName));

        return $qb;
    }

    /**
     * Returns activities for object and target
     *
     * @param mixed $object
     * @param mixed $target
     * @param User $user
     *
     * @return array
     */
    public function findByEntities($object = null, $target = null, User $user = null)
    {
        $qb = $this->createQueryBuilder('a')->addOrderBy('a.date', 'DESC')->addOrderBy('a.id', 'DESC');

        if (null !== $object) {
            $qb = $this->findByObjectFromEntityQueryBuilder($object, $qb);
        }
        if (null !== $target) {
            $qb = $this->findByTargetFromEntityQueryBuilder($target, $qb);
        }
        if (null !== $user) {
            $qb->andWhere('a.user = :user')->setParameter('user', $user);
        }

        return $qb->getQuery()->getResult();
    }
}
