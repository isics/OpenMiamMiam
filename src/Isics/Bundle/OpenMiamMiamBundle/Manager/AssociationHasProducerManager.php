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


use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;

class AssociationHasProducerManager {

    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * Constructs object
     *
     * @param EntityManager   $entityManager
     * @param ActivityManager $activityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager   = $entityManager;
    }

    /**
     * Returns products for association
     *
     * @param Association $association
     *
     * @return array
     */
    public function findForAssociation(Association $association)
    {
        $qb = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:AssociationHasProducer')
            ->getForAssociationQueryBuilder($association);
        $qb->innerjoin('ahp.producer', 'p')
            ->addSelect('p')
            ->innerJoin('p.branches', 'b')
            ->addSelect('b')
            ->addOrderBy('p.name', 'ASC');
        return $qb->getQuery()->getResult();
    }
}