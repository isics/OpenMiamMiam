<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Factory;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\PaymentRepository;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\SalesOrderRepository;
use Isics\Bundle\OpenMiamMiamBundle\Model\Association\AllocatePayment;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

class AllocatePaymentFactory
{
    /**
     * @var PaymentRepository
     */
    private $paymentRepository;

    /**
     * @var SalesOrderRepository
     */
    private $salesOrderRepository;

    /**
     * Constructor
     *
     * @param PaymentRepository    $paymentRepository
     * @param SalesOrderRepository $salesOrderRepository
     */
    public function __construct(PaymentRepository $paymentRepository, SalesOrderRepository $salesOrderRepository)
    {
        $this->paymentRepository    = $paymentRepository;
        $this->salesOrderRepository = $salesOrderRepository;
    }

    /**
     * Create an AllocatePayment model
     *
     * @param Association $association
     * @param User        $user
     *
     * @return AllocatePayment
     */
    public function create(Association $association, User $user = null)
    {
        return new AllocatePayment($association, $user);
    }
}