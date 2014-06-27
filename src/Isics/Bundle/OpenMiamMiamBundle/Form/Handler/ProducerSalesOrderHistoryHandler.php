<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Handler;


use Doctrine\ORM\QueryBuilder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\BranchOccurrenceRepository;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\SalesOrderRepository;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\ProducerSalesOrders;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\ProducerSalesOrdersFilter;
use Symfony\Component\Form\FormFactoryInterface;

class ProducerSalesOrderHistoryHandler
{
    /**
     * @var BranchOccurrenceRepository $branchOccurrenceRepository
     */
    protected $branchOccurrenceRepository;

    /**
     * @var FormFactoryInterface $formFactory
     */
    protected $formFactory;

    /**
     * Constructor
     *
     * @param BranchOccurrenceRepository $branchOccurrenceRepository
     * @param FormFactoryInterface       $formFactory
     */
    public function __construct(BranchOccurrenceRepository $branchOccurrenceRepository, FormFactoryInterface $formFactory)
    {
        $this->branchOccurrenceRepository = $branchOccurrenceRepository;
        $this->formFactory = $formFactory;
    }

    /**
     * Create search form
     *
     * @param Producer $producer
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createSearchForm(Producer $producer)
    {
        return $this->formFactory->create(
            'open_miam_miam_producer_sales_order_search',
            new ProducerSalesOrdersFilter(),
            [
                'producer' => $producer,
            ]
        );
    }

    /**
     * Generate QueryBuilder for search
     *
     * @param Producer $producer
     *
     * @return QueryBuilder
     */
    public function generateQueryBuilder(Producer $producer)
    {
        return $this->branchOccurrenceRepository->getBranchOccurrencesForProducerQueryBuilder($producer);
    }

    /**
     * Applies filter values
     *
     * @param ProducerSalesOrdersFilter $data
     * @param QueryBuilder              $qb
     *
     * @return QueryBuilder
     */
    public function applyFormFilters(ProducerSalesOrdersFilter $data, QueryBuilder $qb)
    {
        $this->branchOccurrenceRepository->filterBranch($qb, $data->getBranch());
        $this->branchOccurrenceRepository->filterDate($qb, $data->getMinDate(), $data->getMaxDate());

        return $qb;
    }
} 