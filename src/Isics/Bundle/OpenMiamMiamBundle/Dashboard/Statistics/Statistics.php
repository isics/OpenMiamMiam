<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Dashboard\Statistics;

class Statistics
{
    protected $lastYear;

    protected $currentYear;

    protected $lastYearValue;

    protected $currentYearValue;

    protected $lastYearData;

    protected $currentYearData;

    public function __construct()
    {
        $this->setLastYear(date('Y') - 1);
        $this->setCurrentYear(date('Y'));
    }

    /**
     * @param string $currentYear
     */
    public function setCurrentYear($currentYear)
    {
        $this->currentYear = $currentYear;
    }

    /**
     * @return string
     */
    public function getCurrentYear()
    {
        return $this->currentYear;
    }

    /**
     * @param array $currentYearData
     */
    public function setCurrentYearData(array $currentYearData)
    {
        $this->currentYearData = $currentYearData;
    }

    /**
     * @return array
     */
    public function getCurrentYearData()
    {
        return $this->currentYearData;
    }

    /**
     * @param string $currentYearValue
     */
    public function setCurrentYearValue($currentYearValue)
    {
        $this->currentYearValue = $currentYearValue;
    }

    /**
     * @return string
     */
    public function getCurrentYearValue()
    {
        return $this->currentYearValue;
    }

    /**
     * @param string $lastYear
     */
    public function setLastYear($lastYear)
    {
        $this->lastYear = $lastYear;
    }

    /**
     * @return string
     */
    public function getLastYear()
    {
        return $this->lastYear;
    }

    /**
     * @param array $lastYearData
     */
    public function setLastYearData(array $lastYearData)
    {
        $this->lastYearData = $lastYearData;
    }

    /**
     * @return array
     */
    public function getLastYearData()
    {
        return $this->lastYearData;
    }

    /**
     * @param string $lastYearValue
     */
    public function setLastYearValue($lastYearValue)
    {
        $this->lastYearValue = $lastYearValue;
    }

    /**
     * @return string
     */
    public function getLastYearValue()
    {
        return $this->lastYearValue;
    }



    /**
     * Returns array representation
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'currentYear'      => $this->getCurrentYear(),
            'lastYear'         => $this->getLastYear(),
            'currentYearValue' => $this->getCurrentYearValue(),
            'lastYearValue'    => $this->getLastYearValue(),
            'currentYearData'  => $this->getCurrentYearData(),
            'lastYearData'     => $this->getLastYearData()
        );
    }
}