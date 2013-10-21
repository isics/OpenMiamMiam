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

use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Newsletter;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;

/**
 * Class NewsletterManager
 * Manager for newsletter
 */
class NewsletterManager
{
    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;
    
    /**
     * @var KernelInterface $kernel
     */
    protected $kernel;
    
    /**
     * @var array $config
     */
    protected $config;

    /**
     * Constructs object
     *
     * @param array $config
     * @param EntityManager $entityManager
     * @param KernelInterface $kernel
     */
    public function __construct(array $config, EntityManager $entityManager, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
    
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->config = $resolver->resolve($config);
    }
    
    /**
     * Returns a new newsletter for a association
     *
     * @param Association $association
     *
     * @return Newsletter
     */
    public function createForAssociation(Association $association)
    {
        $newsletter = new Newsletter();
        $newsletter->setAssociation($association);
        
        return $newsletter;
    }
    
    /**
     * Returns a new newsletter for super
     *
     * @return Newsletter
     */
    public function createForSuper()
    {
        $newsletter = new Newsletter();
        
        return $newsletter;
    }
    /**
     * Saves a newsletter
     *
     * @param Newsletter $newsletter
     */
    public function save(Newsletter $newsletter)
    {
        // Save object
        $this->entityManager->persist($newsletter);
        $this->entityManager->flush();
    }    
}