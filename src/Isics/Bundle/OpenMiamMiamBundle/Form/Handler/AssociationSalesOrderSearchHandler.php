<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Handler;

use Doctrine\ORM\QueryBuilder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\SalesOrderRepository;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\AssociationConsumerSalesOrdersFilter;
use Symfony\Component\Form\FormFactoryInterface;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

class AssociationSalesOrderSearchHandler
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var SalesOrderRepository
     */
    protected $salesOrderRepository;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $formFactory
     * @param SalesOrderRepository $salesOrderRepository
     */
    public function __construct(FormFactoryInterface $formFactory, SalesOrderRepository $salesOrderRepository)
    {
        $this->formFactory          = $formFactory;
        $this->salesOrderRepository = $salesOrderRepository;
    }

    /**
     * Returns a form used to apply filters to a sales orders list
     *
     * @param Association $association
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createSearchForm(Association $association)
    {
        return $this->formFactory->create(
            'open_miam_miam_association_sales_order_search',
            new AssociationConsumerSalesOrdersFilter(),
            [
                'association' => $association,
            ]
        );
    }

    /**
     * Generate a query builder to get sales orders linked to an association and a consumer
     *
     * @param Association $association
     * @param User        $consumer
     * @param int         $limit
     * @param string      $orderBy
     *
     * @return QueryBuilder
     */
    public function generateQueryBuilder(Association $association, User $consumer = null, $limit = null, $orderBy = 'desc')
    {
        return $this->salesOrderRepository->getForAssociationAndConsumerQueryBuilder($association, $consumer, $limit, $orderBy);
    }

    /**
     * Applies filters to the query builder and returns it
     *
     * @param AssociationConsumerSalesOrdersFilter $data
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function applyFormFilters(AssociationConsumerSalesOrdersFilter $data, QueryBuilder $qb)
    {
        $this->salesOrderRepository->filterRef($qb, $data->getRef());
        $this->salesOrderRepository->filterBranch($qb, $data->getBranch());
        $this->salesOrderRepository->filterDate($qb, $data->getMinDate(), $data->getMaxDate());
        $this->salesOrderRepository->filterTotal($qb, $data->getMinTotal(), $data->getMaxTotal());

        return $qb;
    }
} 