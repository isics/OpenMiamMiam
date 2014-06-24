<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder;


use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;

class ProducerSalesOrdersFilter
{
    /**
     * @var BranchOccurrence $branchOccurrence
     */
    protected $branchOccurrence;

    /**
     * @var \DateTime $minDate
     */
    protected $minDate;

    /**
     * @var \DateTime $maxDate
     */
    protected $maxDate;

    /**
     * @var float $minTotal
     */
    protected $minTotal;

    /**
     * @var float $maxTotal
     */
    protected $maxTotal;

    /**
     * @return BranchOccurrence
     */
    public function getBranchOccurrence()
    {
        return $this->branchOccurrence;
    }

    /**
     * @param BranchOccurrence $branchOccurrence
     */
    public function setBranchOccurrence(BranchOccurrence $branchOccurrence)
    {
        $this->branchOccurrence = $branchOccurrence;
    }

    /**
     * @return \DateTime
     */
    public function getMinDate()
    {
        return $this->minDate;
    }

    /**
     * @param \DateTime $minDate
     */
    public function setMinDate(\DateTime $minDate)
    {
        $this->minDate = $minDate;
    }

    /**
     * @return \DateTime
     */
    public function getMaxDate()
    {
        return $this->maxDate;
    }

    /**
     * @param \DateTime $maxDate
     */
    public function setMaxDate(\DateTime $maxDate)
    {
        $this->maxDate = $maxDate;
    }

    /**
     * @return float
     */
    public function getMinTotal()
    {
        return $this->minTotal;
    }

    /**
     * @param float $minTotal
     */
    public function setMinTotal($minTotal)
    {
        $this->minTotal = $minTotal;
    }

    /**
     * @return float
     */
    public function getMaxTotal()
    {
        return $this->maxTotal;
    }

    /**
     * @param float $maxTotal
     */
    public function setMaxTotal($maxTotal)
    {
        $this->maxTotal = $maxTotal;
    }
} 