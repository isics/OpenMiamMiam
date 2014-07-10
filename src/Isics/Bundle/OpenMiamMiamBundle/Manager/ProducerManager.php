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
use Isics\Bundle\OpenMiamMiamBundle\Model\Producer\ProducerWithOwner;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Isics\Bundle\OpenMiamMiamUserBundle\Manager\UserManager;
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
     * @var array $config
     */
    protected $config;

    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * @var KernelInterface $kernel
     */
    protected $kernel;

    /**
     * @var UserManager $userManager
     */
    protected $userManager;

    /**
     * @var ActivityManager $activityManager
     */
    protected $activityManager;


    /**
     * Constructs object
     *
     * @param array $config
     * @param EntityManager $entityManager
     * @param KernelInterface $kernel
     */
    public function __construct(array $config, EntityManager $entityManager, KernelInterface $kernel, UserManager $userManager, ActivityManager $activityManager)
    {
        $this->entityManager   = $entityManager;
        $this->kernel          = $kernel;
        $this->userManager     = $userManager;
        $this->activityManager = $activityManager;

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
     * Creates a producer
     *
     * @return Producer
     */
    public function create()
    {
        $producer = new Producer();

        return $producer;
    }

    /**
     * Saves a producer
     *
     * @param Producer $producer
     * @param User     $user
     */
    public function save(Producer $producer, User $user = null)
    {
        $activityTransKey = null;
        if (null === $producer->getId()) {
            $activityTransKey = 'activity_stream.producer.created';
        } else {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            $unitOfWork->computeChangeSets();

            $changeSet = $unitOfWork->getEntityChangeSet($producer);
            if (!empty($changeSet)) {
                $activityTransKey = 'activity_stream.producer.updated';
            }
        }

        // Save object
        $this->entityManager->persist($producer);
        $this->entityManager->flush();

        // Process image file
        $this->processProfileImageFile($producer);
        $this->processPresentationImageFile($producer);

        // Activity
        if (null !== $activityTransKey) {
            $activity = $this->activityManager->createFromEntities(
                $activityTransKey,
                array('%name%' => $producer->getName()),
                $producer,
                null,
                $user
            );
            $this->entityManager->persist($activity);
            $this->entityManager->flush();
        }
    }

    /**
     * Returns a ProducerWithOwner (DTO)
     *
     * @return ProducerWithOwner
     */
    public function getProducerWithOwner(Producer $producer = null)
    {
        $producerWithOwner = new ProducerWithOwner();

        if (null === $producer) {
            $producerWithOwner->setProducer($this->create());
        } else {
            $producerWithOwner->setProducer($producer);

            if (null !== $owner = $this->userManager->getOwner($producer)) {
                $producerWithOwner->setOwner($owner);
            }
        }

        return $producerWithOwner;
    }

    /**
     * Saves ProducerWithOwner
     *
     * @param ProducerWithOwner $producerWithOwner
     * @param User              $user
     */
    public function saveProducerWithOwner(ProducerWithOwner $producerWithOwner, User $user = null)
    {
        $producer = $producerWithOwner->getProducer();

        $this->save($producer, $user);

        // Set owner
        $this->userManager->setOwner($producer, $producerWithOwner->getOwner());
    }

    /**
     * Removes a producer
     *
     * @param Producer $producer
     */
    public function delete(Producer $producer)
    {
        // Save object
        $producer->setDeletedAt(new \DateTime('now'));
        $this->entityManager->persist($producer);
        $this->entityManager->flush();
    }

    /**
     * Returns ProfileImage path
     *
     * @param Producer $producer
     *
     * @return string
     */
    public function getProfileImagePath(Producer $producer)
    {
        if (null === $producer->getProfileImage()) {
            return null;
        }

        return $this->getUploadDir($producer).'/'.$producer->getProfileImage();
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
     * Processes profileImage file
     *
     * @param Producer $producer
     */
    public function processProfileImageFile(Producer $producer)
    {
        // Delete image if flag is true
        if (null !== $producer->getProfileImage() && $producer->getDeleteProfileImage()) {
            return $this->removeProfileImage($producer);
        }
        // Move new image
        elseif (null !== $producer->getProfileImageFile()) {
            $this->uploadProfileImage($producer);
        }
    }

    /**
     * Removes profileImage file
     *
     * @param Producer $producer
     */
    public function removeProfileImage(Producer $producer)
    {
        if (null !== $producer->getProfileImage()) {
            $this->doRemoveProfileImage($producer);

            $producer->setProfileImage(null);

            $this->entityManager->persist($producer);
            $this->entityManager->flush();
        }
    }

    /**
     * Removes profileImage file
     *
     * @param Producer $producer
     */
    protected function doRemoveProfileImage(Producer $producer)
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove($this->kernel->getRootDir().'/../web'.$this->getProfileImagePath($producer));
    }

    /**
     * Uploads profileImage file
     *
     * @param Producer $producer
     */
    public function uploadProfileImage(Producer $producer)
    {
        $fileSystem = new Filesystem();
        $uploadDir = $this->kernel->getRootDir().'/../web'.$this->getUploadDir($producer);

        // Remove old image
        if (null !== $producer->getProfileImage()) {
            $fileSystem->remove($uploadDir.'/'.$producer->getProfileImage());
        }

        // Move image
        $file = $producer->getProfileImageFile();
        $filename = uniqid($producer->getSlug()).'.'.$file->guessExtension();
        $file->move($uploadDir, $filename);

        // Set new image filename and reset image file
        $producer->setProfileImage($filename);
        $producer->setProfileImageFile(null);

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
        if (null !== $producer->getPresentationImage()) {
            $this->doRemovePresentationImage($producer);

            $producer->setPresentationImage(null);

            $this->entityManager->persist($producer);
            $this->entityManager->flush();
        }
    }

    /**
     * Removes presentationImage file
     *
     * @param Producer $producer
     */
    protected function doRemovePresentationImage(Producer $producer)
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove($this->kernel->getRootDir().'/../web'.$this->getPresentationImagePath($producer));
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

    /**
     * Returns activities of a producer
     *
     * @param Producer $producer
     *
     * @return array
     */
    public function getActivities(Producer $producer)
    {
        return $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Activity')->findByEntities($producer);
    }
}
