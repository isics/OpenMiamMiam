<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 24/06/14
 * Time: 11:13
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder;


use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;

class AssociationConsumerSalesOrdersFilter
{
    /**
     * @var string
     */
    protected $ref;

    /**
     * @var Branch $branch
     */
    protected $branch;

    /**
     * @var \DateTime $minDate
     */
    protected $minDate;

    /**
     * @var \DateTime $maxdate
     */
    protected $maxDate;

    /**
     * @var int $minTotal
     */
    protected $minTotal;

    /**
     * @var int $maxTotal
     */
    protected $maxTotal;

    /**
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @return Branch
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

    /**
     * @return int
     */
    public function getMinTotal()
    {
        return $this->minTotal;
    }

    /**
     * @param $minTotal
     */
    public function setMinTotal($minTotal)
    {
        $this->minTotal = $minTotal;
    }

    /**
     * @return int
     */
    public function getMaxTotal()
    {
        return $this->maxTotal;
    }

    /**
     * @param $maxTotal
     */
    public function setMaxTotal($maxTotal)
    {
        $this->maxTotal = $maxTotal;
    }
} 