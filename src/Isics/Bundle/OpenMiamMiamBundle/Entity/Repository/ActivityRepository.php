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
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ActivityRepository extends EntityRepository
{
    /**
     * Returns activities for object and target
     *
     * @param $object
     * @param $target
     *
     * @return array
     */
    public function findByObjectAndTarget($object, $target)
    {
        $propertyAccessor = new PropertyAccessor();

        $objectMetadata = $this->getEntityManager()->getClassMetadata(get_class($object));
        $objectIdentifierFieldName = $objectMetadata->getSingleIdentifierFieldName();

        $targetMetadata = $this->getEntityManager()->getClassMetadata(get_class($target));
        $targetIdentifierFieldName = $targetMetadata->getSingleIdentifierFieldName();

        return $this->createQueryBuilder('a')
                ->where('a.objectType = :objectType')
                ->setParameter('objectType', $objectMetadata->getName())
                ->andWhere('a.objectId = :objectId')
                ->setParameter('objectId', $propertyAccessor->getValue($object, $objectIdentifierFieldName))
                ->andWhere('a.targetType = :targetType')
                ->setParameter('targetType', $targetMetadata->getName())
                ->andWhere('a.targetId = :targetId')
                ->setParameter('targetId', $propertyAccessor->getValue($target, $targetIdentifierFieldName))
                ->addOrderBy('a.date')
                ->getQuery()
                ->getResult();
    }
}
