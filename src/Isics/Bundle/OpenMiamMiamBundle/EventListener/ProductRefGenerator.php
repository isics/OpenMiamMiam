<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;

class ProductRefGenerator
{
    /**
     * @var string
     *
     * Ref prefix
     */
    protected $refPrefix;

    /**
     * @var integer
     *
     * Ref pad length
     */
    protected $refPadLength;


    /**
     * Constructor
     *
     * @param string $ref_prefix     Reference prefix
     * @param string $ref_pad_length Reference pad length
     */
    public function __construct($ref_prefix, $ref_pad_length)
    {
        $this->refPrefix    = $ref_prefix;
        $this->refPadLength = $ref_pad_length;
    }

    /**
     * Generates reference if null
     *
     * @param LifecycleEventArgs $args Args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity        = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof Product && null === $entity->getRef()) {
            $producer = $entity->getProducer();
            $producer->setProductRefCounter($producer->getProductRefCounter()+1);
            $entityManager->persist($producer);

            $entity->setRef(sprintf(
                '%s%s',
                $this->refPrefix,
                str_pad($producer->getProductRefCounter(), $this->refPadLength, '0', STR_PAD_LEFT)
            ));
        }
    }
}