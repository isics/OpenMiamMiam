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

use Doctrine\ORM\Event\LifecycleEventArgs;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Symfony\Component\Security\Acl\Dbal\AclProvider;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AclManager
{
    /**
     * @var ContainerInterface
     */
    protected $container;


    /**
     * Constructs object
     *
     * Injects Container to avoid cycle reference (ugly!)
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Creates Acl
     *
     * @param LifecycleEventArgs $args Args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $aclProvider = $this->container->get('security.acl.provider');
        $entity = $args->getEntity();
        if ($entity instanceof Producer || $entity instanceof Association) {
            $objectIdentity = ObjectIdentity::fromDomainObject($entity);
            $aclProvider->createAcl($objectIdentity);
        }
    }

    /**
     * Removes Acl
     *
     * @param LifecycleEventArgs $args Args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $aclProvider = $this->container->get('security.acl.provider');
        $entity = $args->getEntity();
        if ($entity instanceof Producer || $entity instanceof Association) {
            $objectIdentity = ObjectIdentity::fromDomainObject($entity);
            $aclProvider->deleteAcl($objectIdentity);
        }
    }
}
