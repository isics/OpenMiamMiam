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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Subscription;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConsumerManager
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
     * Constructor
     *
     * @param array $config
     * @param EntityManager $entityManager
     */
    public function __construct($config, EntityManager $entityManager)
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);

        $this->config = $resolver->resolve($config);
        $this->entityManager = $entityManager;
    }

    /**
     * Set the defaults options
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('ref_prefix', 'ref_pad_length'));
    }

    /**
     * Format consumer ref
     *
     * @param User $user User
     *
     * @return string ref
     */
    public function formatRef(User $user)
    {
        return sprintf(
            '%s%s',
            $this->config['ref_prefix'],
            str_pad($user->getId(), $this->config['ref_pad_length'], '0', STR_PAD_LEFT)
        );
    }

    /**
     * Returns last sales orders linked to an association and a consumer
     *
     * @param Association $association
     * @param null $limit
     * @return mixed
     */
    public function getLastSalesOrderForAssociationAndConsumer(Association $association, User $consumer = null, $limit = null)
    {
        return $this
            ->entityManager
            ->getRepository('IsicsOpenMiamMiamBundle:SalesOrder')
            ->getLastForAssociationAndConsumerQueryBuilder($association, $consumer, $limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns a subscription for an association
     *
     * @param Association $association
     * @param User $consumer
     *
     * @return Subscription
     */
    public function getSubscription(Association $association, User $consumer = null)
    {
        return $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Subscription')
                ->findOneBy(array('association' => $association, 'user' => $consumer));
    }
}
