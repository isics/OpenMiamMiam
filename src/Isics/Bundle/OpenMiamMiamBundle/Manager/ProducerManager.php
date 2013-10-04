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
        $this->processProfilImageFile($producer);
        $this->processPresentationImageFile($producer);
    }

    /**
     * Returns ProfilImage path
     *
     * @param Producer $producer
     *
     * @return string
     */
    public function getProfilImagePath(Producer $producer)
    {
        if (null === $producer->getProfilImage()) {
            return null;
        }

        return $this->getUploadDir($producer).'/'.$producer->getProfilImage();
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
     * Processes profilImage file
     *
     * @param Producer $producer
     */
    public function processProfilImageFile(Producer $producer)
    {
        // Delete image if flag is true
        if (null !== $producer->getProfilImage() && $producer->getDeleteProfilImage()) {
            return $this->removeProfilImage($producer);
        }
        // Move new image
        elseif (null !== $producer->getProfilImageFile()) {
            $this->uploadProfilImage($producer);
        }
    }

    /**
     * Removes profilImage file
     *
     * @param Producer $producer
     */
    public function removeProfilImage(Producer $producer)
    {
        $fileSystem = new Filesystem();
        $uploadDir = $this->kernel->getRootDir().'/../web'.$this->getUploadDir($producer);

        $fileSystem->remove($uploadDir.'/'.$producer->getProfilImage());

        $producer->setProfilImage(null);

        $this->entityManager->persist($producer);
        $this->entityManager->flush();
    }

    /**
     * Uploads profilImage file
     *
     * @param Producer $producer
     */
    public function uploadProfilImage(Producer $producer)
    {
        $fileSystem = new Filesystem();
        $uploadDir = $this->kernel->getRootDir().'/../web'.$this->getUploadDir($producer);

        // Remove old image
        if (null !== $producer->getProfilImage()) {
            $fileSystem->remove($uploadDir.'/'.$producer->getProfilImage());
        }

        // Move image
        $file = $producer->getProfilImageFile();
        $filename = uniqid($producer->getSlug()).'.'.$file->guessExtension();
        $file->move($uploadDir, $filename);

        // Set new image filename and reset image file
        $producer->setProfilImage($filename);
        $producer->setProfilImageFile(null);

        $this->entityManager->persist($producer);
        $this->entityManager->flush();
    }

    /**
     * Returns PresentationImage path
     *
     * @param Producer $producer
     *
     * @return string
     */
    public function getPresentationImagePath(Producer $producer)
    {
        if (null === $producer->getPresentationImage()) {
            return null;
        }
    
        return $this->getUploadDir($producer).'/'.$producer->getPresentationImage();
    }
    
    /**
     * Processes presentationImage file
     *
     * @param Producer $producer
     */
    public function processPresentationImageFile(Producer $producer)
    {
        // Delete image if flag is true
        if (null !== $producer->getPresentationImage() && $producer->getDeletePresentationImage()) {
            return $this->removePresentationImage($producer);
        }
        // Move new image
        elseif (null !== $producer->getPresentationImageFile()) {
            $this->uploadPresentationImage($producer);
        }
    }
    
    /**
     * Removes presentationImage file
     *
     * @param Producer $producer
     */
    public function removePresentationImage(Producer $producer)
    {
        $fileSystem = new Filesystem();
        $uploadDir = $this->kernel->getRootDir().'/../web'.$this->getUploadDir($producer);
    
        $fileSystem->remove($uploadDir.'/'.$producer->getPresentationImage());
    
        $producer->setPresentationImage(null);
    
        $this->entityManager->persist($producer);
        $this->entityManager->flush();
    }
    
    /**
     * Uploads presentationImage file
     *
     * @param Producer $producer
     */
    public function uploadPresentationImage(Producer $producer)
    {
        $fileSystem = new Filesystem();
        $uploadDir = $this->kernel->getRootDir().'/../web'.$this->getUploadDir($producer);
    
        // Remove old image
        if (null !== $producer->getPresentationImage()) {
            $fileSystem->remove($uploadDir.'/'.$producer->getPresentationImage());
        }
    
        // Move image
        $file = $producer->getPresentationImageFile();
        $filename = uniqid($producer->getSlug()).'.'.$file->guessExtension();
        $file->move($uploadDir, $filename);
    
        // Set new image filename and reset image file
        $producer->setPresentationImage($filename);
        $producer->setPresentationImageFile(null);
    
        $this->entityManager->persist($producer);
        $this->entityManager->flush();
    }
}
