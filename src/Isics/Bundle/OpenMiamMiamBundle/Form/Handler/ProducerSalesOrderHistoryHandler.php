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

    public function __construct(BranchOccurrenceRepository $branchOccurrenceRepository, FormFactoryInterface $formFactory)
    {
        $this->branchOccurrenceRepository = $branchOccurrenceRepository;
        $this->formFactory = $formFactory;
    }

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

    public function generateQueryBuilder(Producer $producer)
    {
        return $this->branchOccurrenceRepository->getBranchOccurrencesForProducer($producer);
    }

    public function applyFormFilters(QueryBuilder $queryBuilder, ProducerSalesOrdersFilter $filter)
    {

    }
} 