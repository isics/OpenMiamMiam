<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Handler;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Symfony\Component\Form\FormFactoryInterface;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

class AssociationSalesOrderSearchHandler
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var EntityRepository
     */
    protected $salesOrderRepository;

    public function __construct
    (
        FormFactoryInterface $formFactory,
        EntityRepository $salesOrderRepository
    )
    {
        $this->formFactory          = $formFactory;
        $this->salesOrderRepository = $salesOrderRepository;
    }

    /**
     * @param Journey $journey
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createSearchForm(Association $association)
    {
        return $this->formFactory->create(
            'open_miam_miam_association_sales_order_search',
            null,
            [
                'association' => $association,
            ]
        );
    }

    public function generateQueryBuilder(Association $association, User $consumer)
    {
        return $this->salesOrderRepository->getLastForAssociationAndConsumerQueryBuilder($association, $consumer);
    }

    public function applyFormFilters(array $data, QueryBuilder $qb)
    {
        return $this->salesOrderRepository->filterBranch($qb, $data['branch']);
    }
} 