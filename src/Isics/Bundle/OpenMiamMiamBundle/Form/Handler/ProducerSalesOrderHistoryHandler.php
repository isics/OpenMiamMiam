<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Handler;


use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\SalesOrderRepository;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\ProducerSalesOrdersFilter;

class ProducerSalesOrderHistoryHandler
{
    /**
     * @var SalesOrderRepository $salesOrderRepository
     */
    protected $salesOrderRepository;

    public function __construct(SalesOrderRepository $salesOrderRepository)
    {
        $this->salesOrderRepository = $salesOrderRepository;
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
} 