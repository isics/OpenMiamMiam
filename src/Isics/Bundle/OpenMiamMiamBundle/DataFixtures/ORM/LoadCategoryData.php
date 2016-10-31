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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LoadCategoryData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->translator = $this->container->get('translator');
        $this->translator->setLocale($this->container->getParameter('locale'));
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $root = new Category();
        $root->setName($this->translator->trans('category.root', array(), 'fixtures'));

        $fruitsAndVegetables = new Category();
        $fruitsAndVegetables->setName($this->translator->trans('category.fruits_and_vegetables', array(), 'fixtures'));
        $fruitsAndVegetables->setParent($root);
        $this->addReference('category.fruits_and_vegetables', $fruitsAndVegetables);

        $dairyProduce = new Category();
        $dairyProduce->setName($this->translator->trans('category.dairy_produce', array(), 'fixtures'));
        $dairyProduce->setParent($root);
        $this->addReference('category.dairy_produce', $dairyProduce);

        $meat = new Category();
        $meat->setName($this->translator->trans('category.meat', array(), 'fixtures'));
        $meat->setParent($root);
        $this->addReference('category.meat', $meat);

        $beef = new Category();
        $beef->setName($this->translator->trans('category.beef', array(), 'fixtures'));
        $beef->setParent($meat);
        $this->addReference('category.beef', $beef);

        $lamb = new Category();
        $lamb->setName($this->translator->trans('category.lamb', array(), 'fixtures'));
        $lamb->setParent($meat);
        $this->addReference('category.lamb', $lamb);

        $pork = new Category();
        $pork->setName($this->translator->trans('category.pork', array(), 'fixtures'));
        $pork->setParent($meat);
        $this->addReference('category.pork', $pork);

        $manager->persist($root);
        $manager->persist($fruitsAndVegetables);
        $manager->persist($dairyProduce);
        $manager->persist($meat);
        $manager->persist($beef);
        $manager->persist($lamb);
        $manager->persist($pork);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
