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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ActivityRepository extends EntityRepository
{
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
        $propertyAccessor = new PropertyAccessor();

        $qb = $this->createQueryBuilder('a')->addOrderBy('a.date', 'DESC');

        if (null !== $object) {
            $objectMetadata = $this->getEntityManager()->getClassMetadata(get_class($object));
            $objectIdentifierFieldName = $objectMetadata->getSingleIdentifierFieldName();

            $qb->andWhere('a.objectType = :objectType')
                    ->setParameter('objectType', $objectMetadata->getName())
                    ->andWhere('a.objectId = :objectId')
                    ->setParameter('objectId', $propertyAccessor->getValue($object, $objectIdentifierFieldName));
        }

        if (null !== $target) {
            $targetMetadata = $this->getEntityManager()->getClassMetadata(get_class($target));
            $targetIdentifierFieldName = $targetMetadata->getSingleIdentifierFieldName();

            $qb->andWhere('a.targetType = :targetType')
                    ->setParameter('targetType', $targetMetadata->getName())
                    ->andWhere('a.targetId = :targetId')
                    ->setParameter('targetId', $propertyAccessor->getValue($target, $targetIdentifierFieldName));
        }

        if (null !== $user) {
            $qb->andWhere('a.user = :user')->setParameter('user', $user);
        }

        return $qb->getQuery()->getResult();
    }
}
