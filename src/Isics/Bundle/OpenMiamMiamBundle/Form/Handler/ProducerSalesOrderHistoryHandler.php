<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Handler;


use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\SalesOrderRepository;

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

    
} 