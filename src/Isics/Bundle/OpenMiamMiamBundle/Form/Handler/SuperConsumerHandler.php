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
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\SuperConsumerSearchType;
use Isics\Bundle\OpenMiamMiamBundle\Model\Consumer\AssociationConsumerFilter;
use Isics\Bundle\OpenMiamMiamBundle\Model\Consumer\SuperConsumerFilter;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\Repository\UserRepository;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Symfony\Component\Form\FormFactoryInterface;

class SuperConsumerHandler
{
    /**
     * @var FormFactoryInterface $formFactory
     */
    protected $formFactory;

    /**
     * @var UserRepository $repository
     */
    protected $repository;

    /**
     * @param FormFactoryInterface   $formFactory
     * @param UserRepository $repository
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UserRepository $repository
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
            SuperConsumerSearchType::class,
            new SuperConsumerFilter()
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
     * @return QueryBuilder
     */
    public function generateQueryBuilder()
    {
        return $this->repository->createQueryBuilder('u');
    }

    /**
     * Applies filters values
     *
     * @param QueryBuilder              $qb
     * @param SuperConsumerFilter $data
     *
     * @return QueryBuilder
     */
    public function applyFormFilters(QueryBuilder $qb, SuperConsumerFilter $data)
    {
        $this->repository->refFilter($qb, $data->getRef());
        $this->repository->lastNameFilter($qb, $data->getLastName());
        $this->repository->firstNameFilter($qb, $data->getFirstName());
        $this->repository->deletedFilter($qb, $data->isDeleted());

        return $qb;
    }

    public function applyDefaultFilters(QueryBuilder $qb)
    {
        $this->repository->deletedFilter($qb, false);
    }
}
