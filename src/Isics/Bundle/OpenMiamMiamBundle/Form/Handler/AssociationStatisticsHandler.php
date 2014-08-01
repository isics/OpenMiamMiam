<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Handler;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMapping;
use Isics\Bundle\OpenMiamMiamBundle\Dashboard\Statistics\Statistics;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\SalesOrderRepository;
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\AssociationStatisticsType;
use Sonata\IntlBundle\Templating\Helper\NumberHelper;

class AssociationStatisticsHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var NumberHelper
     */
    protected $numberHelper;

    /**
     * @var string
     */
    protected $currency;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     * @param NumberHelper  $numberHelper
     * @param string        $currency
     */
    public function __construct(EntityManager $entityManager, NumberHelper $numberHelper, $currency)
    {
        $this->entityManager = $entityManager;
        $this->numberHelper  = $numberHelper;
        $this->currency      = $currency;
    }

    /**
     * Compute statistics data related to filtered values
     *
     * @param Association $association
     * @param array       $values
     *
     * @throws \InvalidArgumentException
     * @return Statistics
     */
    public function getData(Association $association, array $values)
    {
        switch ($values['mode']) {
            case AssociationStatisticsType::MODE_TURNOVER:
                return $this->getTurnoverData($association, $values);
            case AssociationStatisticsType::MODE_COMMISSION:
                return $this->getCommissionData($association, $values);
            case AssociationStatisticsType::MODE_SALES_ORDERS:
                return $this->getSalesOrdersData($association, $values);
            case AssociationStatisticsType::MODE_AVERAGE_CART:
                return $this->getAverageCartData($association, $values);
        }

        throw new \InvalidArgumentException('');
    }

    /**
     * @param Association $association
     * @param array       $values
     *
     * @return array
     */
    protected function getTurnoverData(Association $association, array $values)
    {
        $sql = <<<SQL
            SELECT
                MONTH(bo.begin) AS month,
                DAY(bo.begin) AS day,
                SUM(so.total) AS value
            FROM
                sales_order so
            INNER JOIN branch_occurrence bo ON (so.branch_occurrence_id = bo.id)
            INNER JOIN branch b ON (bo.branch_id = b.id)
            WHERE
                b.association_id = :association_id
                AND
                bo.begin BETWEEN :fromDate AND :toDate
SQL;
        $parameters = array(
            'association_id' => $association->getId()
        );

        if ($values['branch'] instanceof Branch) {
            $sql .= ' AND b.id = :branchId';
            $parameters['branchId'] = $values['branch']->getId();
        }

        $sql .= <<<SQL
            GROUP BY DATE(bo.begin)
            ORDER BY bo.begin ASC
SQL;

        $statistics = new Statistics();

        $lastYear     = (string)(date('Y') - 1);
        $lastYearData = $this->indexArray($this->entityManager->getConnection()->fetchAll($sql, array_merge($parameters, array(
            'fromDate'       => $lastYear . '-01-01 00:00:00',
            'toDate'         => $lastYear . '-12-31 23:59:59'
        ))));

        $currentYear     = (string)(date('Y'));
        $currentYearData = $this->indexArray($this->entityManager->getConnection()->fetchAll($sql, array_merge($parameters, array(
            'fromDate'       => $currentYear . '-01-01 00:00:00',
            'toDate'         => $currentYear . '-12-31 23:59:59'
        ))));

        $statistics->setLastYearData($lastYearData);
        $statistics->setLastYearValue($this->numberHelper->formatCurrency(array_sum(array_map(function ($row) {
            return $row['value'];
        }, $lastYearData)), $this->currency));
        $statistics->setCurrentYearData($currentYearData);
        $statistics->setCurrentYearValue(array_sum($currentYearData));
        $statistics->setCurrentYearValue($this->numberHelper->formatCurrency(array_sum(array_map(function ($row) {
            return $row['value'];
        }, $currentYearData)), $this->currency));

        return $statistics;
    }

    /**
     * @param Association $association
     * @param array       $values
     *
     * @return array
     */
    protected function getCommissionData(Association $association, array $values)
    {
        $sql = <<<SQL
            SELECT
                MONTH(bo.begin) AS month,
                DAY(bo.begin) AS day,
                SUM(sor.total * sor.commission / 100) AS value
            FROM
                sales_order_row sor
            INNER JOIN sales_order so ON (sor.sales_order_id = so.id)
            INNER JOIN branch_occurrence bo ON (so.branch_occurrence_id = bo.id)
            INNER JOIN branch b ON (bo.branch_id = b.id)
            WHERE
                b.association_id = :association_id
                AND
                bo.begin BETWEEN :fromDate AND :toDate
SQL;
        $parameters = array(
            'association_id' => $association->getId()
        );

        if ($values['branch'] instanceof Branch) {
            $sql .= ' AND b.id = :branchId';
            $parameters['branchId'] = $values['branch']->getId();
        }

        $sql .= <<<SQL
            GROUP BY DATE(bo.begin)
            ORDER BY bo.begin ASC
SQL;

        $statistics = new Statistics();

        $lastYear     = (string)(date('Y') - 1);
        $lastYearData = $this->indexArray($this->entityManager->getConnection()->fetchAll($sql, array_merge($parameters, array(
            'fromDate'       => $lastYear . '-01-01 00:00:00',
            'toDate'         => $lastYear . '-12-31 23:59:59'
        ))));

        $currentYear     = (string)(date('Y'));
        $currentYearData = $this->indexArray($this->entityManager->getConnection()->fetchAll($sql, array_merge($parameters, array(
            'fromDate'       => $currentYear . '-01-01 00:00:00',
            'toDate'         => $currentYear . '-12-31 23:59:59'
        ))));

        $statistics->setLastYearData($lastYearData);
        $statistics->setLastYearValue($this->numberHelper->formatCurrency(array_sum(array_map(function ($row) {
            return $row['value'];
        }, $lastYearData)), $this->currency));
        $statistics->setCurrentYearData($currentYearData);
        $statistics->setCurrentYearValue(array_sum($currentYearData));
        $statistics->setCurrentYearValue($this->numberHelper->formatCurrency(array_sum(array_map(function ($row) {
            return $row['value'];
        }, $currentYearData)), $this->currency));

        return $statistics;
    }

    /**
     * @param Association $association
     * @param array       $values
     *
     * @return array
     */
    protected function getSalesOrdersData(Association $association, array $values)
    {
        $sql = <<<SQL
            SELECT
                MONTH(bo.begin) AS month,
                DAY(bo.begin) AS day,
                COUNT(so.id) AS value
            FROM
                sales_order so
            INNER JOIN branch_occurrence bo ON (so.branch_occurrence_id = bo.id)
            INNER JOIN branch b ON (bo.branch_id = b.id)
            WHERE
                b.association_id = :association_id
                AND
                bo.begin BETWEEN :fromDate AND :toDate
SQL;
        $parameters = array(
            'association_id' => $association->getId()
        );

        if ($values['branch'] instanceof Branch) {
            $sql .= ' AND b.id = :branchId';
            $parameters['branchId'] = $values['branch']->getId();
        }

        $sql .= <<<SQL
            GROUP BY DATE(bo.begin)
            ORDER BY bo.begin ASC
SQL;

        $statistics = new Statistics();

        $lastYear     = (string)(date('Y') - 1);
        $lastYearData = $this->indexArray($this->entityManager->getConnection()->fetchAll($sql, array_merge($parameters, array(
            'fromDate'       => $lastYear . '-01-01 00:00:00',
            'toDate'         => $lastYear . '-12-31 23:59:59'
        ))));

        $currentYear     = (string)(date('Y'));
        $currentYearData = $this->indexArray($this->entityManager->getConnection()->fetchAll($sql, array_merge($parameters, array(
            'fromDate'       => $currentYear . '-01-01 00:00:00',
            'toDate'         => $currentYear . '-12-31 23:59:59'
        ))));

        $statistics->setLastYearData($lastYearData);
        $statistics->setLastYearValue($this->numberHelper->formatDecimal(array_sum(array_map(function ($row) {
            return $row['value'];
        }, $lastYearData))));
        $statistics->setCurrentYearData($currentYearData);
        $statistics->setCurrentYearValue(array_sum($currentYearData));
        $statistics->setCurrentYearValue($this->numberHelper->formatDecimal(array_sum(array_map(function ($row) {
            return $row['value'];
        }, $currentYearData))));

        return $statistics;
    }

    /**
     * @param Association $association
     * @param array       $values
     *
     * @return array
     */
    protected function getAverageCartData(Association $association, array $values)
    {
        $sql = <<<SQL
            SELECT
                MONTH(bo.begin) AS month,
                DAY(bo.begin) AS day,
                SUM(so.total) / COUNT(so.id) AS value
            FROM
                sales_order so
            INNER JOIN branch_occurrence bo ON (so.branch_occurrence_id = bo.id)
            INNER JOIN branch b ON (bo.branch_id = b.id)
            WHERE
                b.association_id = :association_id
                AND
                bo.begin BETWEEN :fromDate AND :toDate
SQL;
        $parameters = array(
            'association_id' => $association->getId()
        );

        if ($values['branch'] instanceof Branch) {
            $sql .= ' AND b.id = :branchId';
            $parameters['branchId'] = $values['branch']->getId();
        }

        $sql .= <<<SQL
            GROUP BY DATE(bo.begin)
            ORDER BY bo.begin ASC
SQL;

        $statistics = new Statistics();

        $lastYear     = (string)(date('Y') - 1);
        $lastYearData = $this->indexArray($this->entityManager->getConnection()->fetchAll($sql, array_merge($parameters, array(
            'fromDate'       => $lastYear . '-01-01 00:00:00',
            'toDate'         => $lastYear . '-12-31 23:59:59'
        ))));

        $currentYear     = (string)(date('Y'));
        $currentYearData = $this->indexArray($this->entityManager->getConnection()->fetchAll($sql, array_merge($parameters, array(
            'fromDate'       => $currentYear . '-01-01 00:00:00',
            'toDate'         => $currentYear . '-12-31 23:59:59'
        ))));

        $statistics->setLastYearData($lastYearData);
        $statistics->setLastYearValue($this->numberHelper->formatCurrency(array_sum(array_map(function ($row) {
            return $row['value'];
        }, $lastYearData)) / count($lastYearData), $this->currency));
        $statistics->setCurrentYearData($currentYearData);
        $statistics->setCurrentYearValue(array_sum($currentYearData));
        $statistics->setCurrentYearValue($this->numberHelper->formatCurrency(array_sum(array_map(function ($row) {
            return $row['value'];
        }, $currentYearData)) / count($lastYearData), $this->currency));

        return $statistics;
    }

    /**
     * Index array data
     *
     * @param $values
     *
     * @return array
     */
    protected function indexArray($values)
    {
        $data = array();

        foreach ($values as $value) {
            $key        = $value['month'] . '-' . $value['day'];
            $data[$key] = $value;
        }

        return $data;
    }
}