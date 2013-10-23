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
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

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
     * @var ActivityManager $activityManager
     */
    protected $activityManager;

    /**
     * Constructs object
     *
     * @param EntityManager $entityManager
     * @param ActivityManager $activityManager
     */
    public function __construct(EntityManager $entityManager, ActivityManager $activityManager)
    {
        $this->entityManager = $entityManager;
        $this->activityManager = $activityManager;
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
        $activityTransKey = null;
        if (null === $newsletter->getId()) {
            $activityTransKey = 'activity_stream.newsletter.created';
        } else {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            $unitOfWork->computeChangeSets();

            $changeSet = $unitOfWork->getEntityChangeSet($newsletter);
            if (!empty($changeSet)) {
                $activityTransKey = 'activity_stream.newsletter.updated';
            }
        }

        // Save object
        $this->entityManager->persist($newsletter);
        $this->entityManager->flush();

        // Activity
        if (null !== $activityTransKey) {
            $activity = $this->activityManager->createFromEntities(
                $activityTransKey,
               // array('%title%' => $newsletter->
            );
            $this->entityManager->persist($activity);
            $this->entityManager->flush();
        }
    }

    public function send(Newsletter $newsletter)
    {
        
        $body = $this->engine->render('IsicsOpenMiamMiamBundle:Mail:newsletterEmail.html.twig', array('body' => $newsletter->getBody()));

        $recipientType = $newsletter->getRecipientType();

        if($recipientType === Newsletter::RECIPIENT_TYPE_CONSUMER)
        {
            if($newsletter->hasAssociation() == true)
            {
                $to = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:User')->findConsumerForAssociation($newsletter->getAssociations());
            }
            else
            {
                $to = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:User')->findConsumerForBranch($newsletter->getBranches());
            } 
        }
        else if($recipientType === Newsletter::RECIPIENT_TYPE_PRODUCER)
        {
            if($newsletter->hasAssociation() == true)
            {
                $to = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:User')->findProducerForAssociation($newsletter->getAssociations());
            }
            else
            {
                $to = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:User')->findProducerForBranch($newsletter->getBranches());
            } 
        }
        else
        {
            $to = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:User')->FindAll(
                    $newsletter->getAssociations(), 
                    $newsletter->getBranches()
                );  
        }

        $message = \Swift_Message::newInstance()
            ->setFrom(array($this->mailerConfig['sender_address'] => $this->mailerConfig['sender_name']))
            ->setTo($to)
            ->setSubject($newsletter->getSubject())
            ->setBody($body, 'text/html');

        $this->mailer->send($message);

    }  
    public function sendTest(Newsletter $newsletter, $user)
    {
        $body = $this->engine->render('IsicsOpenMiamMiamBundle:Mail:newsletterTestEmail.html.twig', array('body' => $newsletter->getBody()));

        $message = \Swift_Message::newInstance()
            ->setFrom(array($this->mailerConfig['sender_address'] => $this->mailerConfig['sender_name']))
            ->setTo($user->getEmail())
            ->setSubject($newsletter->getSubject())
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
    }
}