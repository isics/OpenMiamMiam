<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Manager;

use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Model\Association\AllocatePayment;

class AllocatePaymentManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Save user (or anonymous) payments allocation
     *
     * @param AllocatePayment $allocatePayment
     */
    public function process(AllocatePayment $allocatePayment)
    {

    }
}