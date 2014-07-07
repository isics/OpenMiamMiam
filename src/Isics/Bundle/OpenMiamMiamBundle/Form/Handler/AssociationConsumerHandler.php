<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Handler;

use Doctrine\ORM\QueryBuilder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\SubscriptionRepository;
use Isics\Bundle\OpenMiamMiamBundle\Model\Consumer\AssociationConsumerFilter;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Symfony\Component\Form\FormFactoryInterface;

class AssociationConsumerHandler
{
    /**
     * @var FormFactoryInterface $formFactory
     */
    protected $formFactory;

    /**
     * @var SubscriptionRepository $repository
     */
    protected $repository;

    /**
     * @param FormFactoryInterface   $formFactory
     * @param SubscriptionRepository $repository
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        SubscriptionRepository $repository
    )
    {
        $this->formFactory  = $formFactory;
        $this->repository   = $repository;
    }

    /**
     * Returns a form used to apply filters to a consumers list
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createSearchForm()
    {
        return $this->formFactory->create(
            'open_miam_miam_association_consumer_search',
            new AssociationConsumerFilter()
        );
    }

    /**
     * Returns a user profile form
     *
     * @param User $consumer
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createProfileForm(User $consumer)
    {
        return $this->formFactory->create(
            'open_miam_miam_user_profile',
            $consumer
        );
    }

    /**
     * Generate the QueryBuilder for search form
     *
     * @param Association $association
     *
     * @return QueryBuilder
     */
    public function generateQueryBuilder(Association $association)
    {
        return $this->repository->getForAssociationQueryBuilder($association);
    }

    /**
     * Applies filters values
     *
     * @param QueryBuilder              $qb
     * @param AssociationConsumerFilter $data
     *
     * @return QueryBuilder
     */
    public function applyFormFilters(QueryBuilder $qb, AssociationConsumerFilter $data)
    {
        $this->repository->refFilter($qb, $data->getRef());
        $this->repository->lastNameFilter($qb, $data->getLastName());
        $this->repository->firstNameFilter($qb, $data->getFirstName());
        $this->repository->creditorFilter($qb, $data->isCreditor());

        return $qb;
    }
} 