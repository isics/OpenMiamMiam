<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Twig;


use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Manager\ProducerSalesOrderManager;

class ProducerExtension extends \Twig_Extension
{
    /**
     * @var ProducerSalesOrderManager $producerSalesOrderManager
     */
    protected $producerSalesOrderManager;

    public function __construct(ProducerSalesOrderManager $producerSalesOrderManager)
    {
        $this->producerSalesOrderManager = $producerSalesOrderManager;
    }

    /**
     * Returns available functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('get_producer_sales_orders_for_branch_occurrence', [$this, 'getProducerSalesOrdersForBranchOccurrence']),
        ];
    }

    public function getProducerSalesOrdersForBranchOccurrence(Producer $producer, BranchOccurrence $branchOccurrence)
    {
        return $this->producerSalesOrderManager->getForBranchOccurrence($producer, $branchOccurrence);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'open_miam_miam_producer_extension';
    }

}