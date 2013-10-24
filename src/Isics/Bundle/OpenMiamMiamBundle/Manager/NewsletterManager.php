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
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class NewsletterManager
 * 
 * @package Isics\Bundle\OpenMiamMiamBundle\Manager
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
     * @var \Swift_mailer
     */
    protected $mailer;

    /**
     * @var array $mailerConfig
     */
    protected $mailerConfig;

    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * Constructs object
     *
     * @param EntityManager     $entityManager
     * @param ActivityManager   $activityManager
     * @param \Swift_Mailer     $mailer
     * @param array             $mailerConfig
     * @param EngineInterface   $engine
     */
    public function __construct(EntityManager $entityManager, 
                                ActivityManager $activityManager,
                                \Swift_Mailer $mailer,
                                array $mailerConfig,
                                EngineInterface $engine)
    {
        $this->entityManager = $entityManager;
        $this->activityManager = $activityManager;
        $this->mailer = $mailer;
        $this->engine = $engine;

        $resolver = new OptionsResolver();
        $this->setMailerConfigResolverDefaultOptions($resolver);
        $this->mailerConfig = $resolver->resolve($mailerConfig);
    }
    
    /**
     * Set the defaults options
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function setMailerConfigResolverDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('sender_name', 'sender_address'));
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
     * @param User       $user
     */
    public function save(Newsletter $newsletter, User $user = null)
    {
        $association = $newsletter->getAssociation();

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
                array('%title%' => $newsletter->getSubject()),
                $newsletter,
                $association,
                $user
            );
            $this->entityManager->persist($activity);
            $this->entityManager->flush();
        }
    }

    /**
     * Send email to consumer and/or producer
     *
     * @param Newsletter $newsletter
     */
    public function send(Newsletter $newsletter)
    {

        $body = $this->engine->render('IsicsOpenMiamMiamBundle:Mail:newsletterEmail.html.twig', array('newsletter' => $newsletter));

        $recipientType = $newsletter->getRecipientType();

        if($recipientType === Newsletter::RECIPIENT_TYPE_CONSUMER)
        {
            if($newsletter->hasAssociation() == true)
            {
                $to = $this->entityManager->getRepository('IsicsOpenMiamMiamUserBundle:User')->findConsumerForAssociation($newsletter->getAssociations());
            }
            else
            {
                $to = $this->entityManager->getRepository('IsicsOpenMiamMiamUserBundle:User')->findConsumerForBranch($newsletter->getBranches());
            } 
        }
        else if($recipientType === Newsletter::RECIPIENT_TYPE_PRODUCER)
        {
            if($newsletter->hasAssociation() == true)
            {
                $to = $this->entityManager->getRepository('IsicsOpenMiamMiamUserBundle:User')->findProducerForAssociation($newsletter->getAssociations());
            }
            else
            {
                $to = $this->entityManager->getRepository('IsicsOpenMiamMiamUserBundle:User')->findProducerForBranch($newsletter->getBranches());
            } 
        }
        else
        {
            $to = $this->entityManager->getRepository('IsicsOpenMiamMiamUserBundle:User')->FindAll(
                    $newsletter->getAssociations(), 
                    $newsletter->getBranches()
                );  
        }

        $message = \Swift_Message::newInstance()
            ->setFrom(array($this->mailerConfig['sender_address'] => $this->mailerConfig['sender_name']))
            ->setTo('anciaux.dimitri.lycee@gmail.com')
            ->setSubject($newsletter->getSubject())
            ->setBody($body, 'text/html');

        $this->mailer->send($message);

    }

    /**
     * Send Test email Super
     *
     * @param Newsletter  $newsletter
     * @param User        $user
     * 
     */
    public function sendTestSuper(Newsletter $newsletter, User $user)
    {
        $body = $this->engine->render('IsicsOpenMiamMiamBundle:Mail:newsletterTestEmail.html.twig', array('newsletter' => $newsletter));

        $message = \Swift_Message::newInstance()
            ->setFrom(array($this->mailerConfig['sender_address'] => $this->mailerConfig['sender_name']))
            ->setTo('anciaux.dimitri.lycee@gmail.com')
            ->setSubject($newsletter->getSubject())
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
    }

    /**
     * Send Test email Association
     *
     * @param Newsletter  $newsletter
     * @param User        $user
     * @param Association $association
     */
    public function sendTest(Newsletter $newsletter, User $user, Association $association)
    {
        $body = $this->engine->render('IsicsOpenMiamMiamBundle:Mail:newsletterTestEmail.html.twig', array('newsletter' => $newsletter, 'association' => $association));

        $message = \Swift_Message::newInstance()
            ->setFrom(array($this->mailerConfig['sender_address'] => $this->mailerConfig['sender_name']))
            ->setTo('anciaux.dimitri.lycee@gmail.com')
            ->setSubject($newsletter->getSubject())
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
    }

    /**
     * Returns activities of a newsletter
     *
     * @param Newsletter $newsletter
     *
     * @return array
     */
    public function getActivities(Newsletter $newsletter)
    {
        return $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Activity')->findByEntities($newsletter, $newsletter->getAssociation());
    }
}