<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Handler;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Symfony\Component\Form\FormFactoryInterface;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

class AssociationSalesOrderSearchHandler
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var EntityRepository
     */
    protected $salesOrderRepository;

    public function __construct
    (
        FormFactoryInterface $formFactory,
        EntityRepository $salesOrderRepository
    )
    {
        $this->formFactory          = $formFactory;
        $this->salesOrderRepository = $salesOrderRepository;
    }

    /**
     * Returns a form used to apply filters to a sales orders list
     *
     * @param Journey $journey
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createSearchForm(Association $association)
    {
        return $this->formFactory->create(
            'open_miam_miam_association_sales_order_search',
            null,
            [
                'association' => $association,
            ]
        );
    }

    /**
     * Generate a query builder to get sales orders linked to an association and a consumer
     *
     * @param Association $association
     * @param User $consumer
     *
     * @return mixed
     */
    public function generateQueryBuilder(Association $association, User $consumer = null)
    {
        return $this->salesOrderRepository->getLastForAssociationAndConsumerQueryBuilder($association, $consumer);
    }

    /**
     * Applies filters to the query builder and returns it
     *
     * @param array $data
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function applyFormFilters(array $data, QueryBuilder $qb)
    {
        $this->salesOrderRepository->filterBranch($qb, $data['branch']);
        $this->salesOrderRepository->filterDate($qb, $data['minDate'], $data['maxDate']);
        $this->salesOrderRepository->filterTotal($qb, $data['minTotal'], $data['maxTotal']);

        return $qb;
    }
} 