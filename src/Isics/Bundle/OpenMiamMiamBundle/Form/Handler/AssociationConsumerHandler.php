<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Handler;


use Doctrine\ORM\QueryBuilder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\SubscriptionRepository;
use Isics\Bundle\OpenMiamMiamBundle\Model\Consumer\AssociationConsumerFilter;
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
        return $this->formFactory->create(
            'open_miam_miam_association_consumer_search',
            new AssociationConsumerFilter()
        );
    }

    public function generateQueryBuilder(Association $association)
    {
        return $this->repository->getForAssociationQueryBuilder($association);
    }

    public function applyFormFilters(QueryBuilder $qb, AssociationConsumerFilter $data)
    {
        $this->repository->refFilter($qb, $data->getRef());
        $this->repository->lastNameFilter($qb, $data->getLastName());
        $this->repository->firstNameFilter($qb, $data->getFirstName());
        $this->repository->creditorFilter($qb, $data->isCreditor());

        return $qb;
    }
} 