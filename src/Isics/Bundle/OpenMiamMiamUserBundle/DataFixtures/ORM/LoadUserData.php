<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamUserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

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
        $userManager = $this->container->get('fos_user.user_manager');

        # Admin
        $user = $userManager->createUser();
        $user->setUsername('admin@domain.tld');
        $user->setPlainPassword('admin');
        $user->setEmail('admin@domain.tld');
        $user->setEnabled(true);
        $user->setSuperAdmin(true);
        $user->setFirstname('Admin');
        $user->setLastname('Admin');
        $user->setAddress1('First line of address');
        $user->setAddress2('Second line of address');
        $user->setZipcode('AA9A 9AA');
        $user->setCity('York');
        $userManager->updateUser($user);

        # User 1
        $user = $userManager->createUser();
        $user->setUsername('foo@bar.com');
        $user->setPlainPassword('secret3');
        $user->setEmail('foo@bar.com');
        $user->setEnabled(true);
        $user->addRole('ROLE_ADMIN');
        $user->setFirstname('Foo');
        $user->setLastname('Bar');
        $user->setAddress1('First line of address');
        $user->setAddress2('Second line of address');
        $user->setZipcode('AA9A 9AA');
        $user->setCity('York');
        $userManager->updateUser($user);

        # User 2
        $user = $userManager->createUser();
        $user->setUsername('john@doe.com');
        $user->setPlainPassword('secret1');
        $user->setEmail('john@doe.com');
        $user->setEnabled(true);
        $user->setSuperAdmin(false);
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $user->setAddress1('First line of address');
        $user->setAddress2('Second line of address');
        $user->setZipcode('AA9A 9AA');
        $user->setCity('London');
        $userManager->updateUser($user);

        # User 3
        $user = $userManager->createUser();
        $user->setUsername('john@smith.com');
        $user->setPlainPassword('secret2');
        $user->setEmail('john@smith.com');
        $user->setEnabled(true);
        $user->setSuperAdmin(false);
        $user->setFirstname('John');
        $user->setLastname('Smith');
        $user->setAddress1('First line of address');
        $user->setAddress2('Second line of address');
        $user->setZipcode('AA9A 9AA');
        $user->setCity('Liverpool');
        $userManager->updateUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
