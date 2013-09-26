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

use Doctrine\Common\Persistence\ObjectManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ProducerManager
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Manager
 */
class ProducerManager
{
    /**
     * @var ObjectManager $objectManager
     */
    protected $objectManager;

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
     * @param ObjectManager $objectManager
     * @param KernelInterface $kernel
     */
    public function __construct(array $config, ObjectManager $objectManager, KernelInterface $kernel)
    {
        $this->objectManager = $objectManager;
        $this->kernel = $kernel;

        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->config = $resolver->resolve($config);
    }

    /**
     * Set the defaults options
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('nb_next_producer_attendances_to_define','upload_path'));
    }
    
    /**
     * Saves a product
     *
     * @param Producer $producer
     */
    public function save(Producer $producer)
    {
    	// Save object
    	$this->objectManager->persist($producer);
    	$this->objectManager->flush();
    
    	// Process image file
    	$this->processImageFile($producer);
    }



    /**
     * Returns image path
     *
     * @param Producer $producer
     *
     * @return string
     */
    public function getImagePath(Producer $producer)
    {
        if (null === $producer->getImage()) {
            return null;
        }

        return $this->getUploadDir($producer).'/'.$producer->getImage();
    }

    /**
     * Returns upload directory
     *
     * @param Producer $producer
     *
     * @throws \DomainException
     *
     * @return string
     */
    public function getUploadDir(Producer $producer)
    {
        if (!$producer instanceof Producer) {
            throw new \DomainException();
        }

        return $this->config['upload_path'].'/'.$producer->getId();
    }

    /**
     * Processes image file
     *
     * @param Producer $producer
     */
    public function processImageFile(Producer $producer)
    {
        // Delete image if flag is true
        if (null !== $producer->getImage() && $producer->getDeleteImage()) {
            return $this->removeImage($producer);
        }
        // Move new image
        elseif (null !== $producer->getImageFile()) {
            $this->uploadImage($producer);
        }
    }

    /**
     * Removes image file
     *
     * @param Producer $producer
     */
    public function removeImage(Producer $producer)
    {
        $fileSystem = new Filesystem();
        $uploadDir = $this->kernel->getRootDir().'/../web'.$this->getUploadDir($producer);

        $fileSystem->remove($uploadDir.'/'.$producer->getImage());

        $producer->setImage(null);

        $this->objectManager->persist($producer);
        $this->objectManager->flush();
    }

    /**
     * Uploads image file
     *
     * @param Producer $producer
     */
    public function uploadImage(Producer $producer)
    {
        $fileSystem = new Filesystem();
        $uploadDir = $this->kernel->getRootDir().'/../web'.$this->getUploadDir($producer);

        // Remove old image
        if (null !== $producer->getImage()) {
            $fileSystem->remove($uploadDir.'/'.$producer->getImage());
        }

        // Move image
        $file = $producer->getImageFile();
        $filename = uniqid($producer->getSlug()).'.'.$file->guessExtension();
        $file->move($uploadDir, $filename);

        // Set new image filename and reset image file
        $producer->setImage($filename);
        $producer->setImageFile(null);

        $this->objectManager->persist($producer);
        $this->objectManager->flush();
    }

   
}
