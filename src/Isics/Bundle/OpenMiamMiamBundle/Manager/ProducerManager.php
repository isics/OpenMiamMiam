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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Symfony\Component\Filesystem\Filesystem;
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
     * Set the defaults options
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('nb_next_producer_attendances_to_define', 'upload_path'));
    }

    /**
     * Saves a producer
     *
     * @param Producer $producer
     */
    public function save(Producer $producer)
    {
        // Save object
        $this->entityManager->persist($producer);
        $this->entityManager->flush();

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
     * @return string
     */
    public function getUploadDir()
    {
        return $this->config['upload_path'];
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

        $this->entityManager->persist($producer);
        $this->entityManager->flush();
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

        $this->entityManager->persist($producer);
        $this->entityManager->flush();
    }
}
