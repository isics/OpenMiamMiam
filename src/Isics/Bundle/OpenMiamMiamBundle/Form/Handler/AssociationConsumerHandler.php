<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Handler;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\SubscriptionRepository;
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
     * @param Association $association
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createSearchForm()
    {
        return $this->formFactory->create('open_miam_miam_association_consumer_search');
    }

    public function applyFormFilters(QueryBuilder $qb, $data)
    {
        $this->repository->refFilter($qb, $data['ref']);
        $this->repository->lastNameFilter($qb, $data['lastName']);
        $this->repository->firstNameFilter($qb, $data['firstName']);
        $this->repository->creditorFilter($qb, $data['creditor']);

        return $qb;
    }
} 