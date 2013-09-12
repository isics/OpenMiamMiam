<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        # Services
        $manipulator = $this->container->get('fos_user.util.user_manipulator');
        $aclProvider = $this->container->get('security.acl.provider');

        # ACLs
        $objectIdentity1 = ObjectIdentity::fromDomainObject($this->getReference('producer Beth Rave'));
        $acl1 = $aclProvider->findAcl($objectIdentity1);

        $objectIdentity2 = ObjectIdentity::fromDomainObject($this->getReference('producer Elsa Dorsa'));
        $acl2 = $aclProvider->findAcl($objectIdentity2);


        # User 1
        $manipulator->create("foo@bar.com", "secret3", "foo@bar.com", true, false);

        # User 2
        $manipulator->create("john@doe.com", "secret1", "john@doe.com", true, false);
        $securityIdentity1 = new UserSecurityIdentity('john@doe.com', 'Isics\Bundle\OpenMiamMiamBundle\Entity\User');
        $acl1->insertObjectAce($securityIdentity1, MaskBuilder::MASK_OWNER);
        $aclProvider->updateAcl($acl1);

        # User 3
        $manipulator->create("john@smith.com", "secret2", "john@smith.com", true, false);
        $securityIdentity2 = new UserSecurityIdentity('john@smith.com', 'Isics\Bundle\OpenMiamMiamBundle\Entity\User');
        $acl1->insertObjectAce($securityIdentity2, MaskBuilder::MASK_OWNER);
        $aclProvider->updateAcl($acl1);

        $acl2->insertObjectAce($securityIdentity2, MaskBuilder::MASK_OWNER);
        $aclProvider->updateAcl($acl2);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 8;
    }
}
