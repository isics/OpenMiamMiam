<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder;


use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;

class ProducerSalesOrdersFilter
{
    /**
     * @var Branch $branch
     */
    protected $branch;

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
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @param Branch $branch
     */
    public function setBranch(Branch $branch = null)
    {
        $this->branch = $branch;
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
    public function setMinDate(\DateTime $minDate = null)
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
    public function setMaxDate(\DateTime $maxDate = null)
    {
        $this->maxDate = $maxDate;
    }
} 