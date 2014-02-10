<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Payment;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\PaymentRepository;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\SalesOrderRepository;
use Isics\Bundle\OpenMiamMiamBundle\Manager\ConsumerManager;
use Isics\Bundle\OpenMiamMiamBundle\Manager\PaymentManager;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class AllocatePaymentType extends AbstractType
{
    /**
     * @var ConsumerManager
     */
    private $consumerManager;

    /**
     * @var PaymentManager
     */
    private $paymentManager;

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
     * @param ConsumerManager      $consumerManager
     * @param PaymentRepository    $paymentRepository
     * @param SalesOrderRepository $salesOrderRepository
     */
    public function __construct(ConsumerManager $consumerManager,
                                PaymentManager $paymentManager,
                                PaymentRepository $paymentRepository,
                                SalesOrderRepository $salesOrderRepository)
    {
        $this->consumerManager      = $consumerManager;
        $this->paymentManager       = $paymentManager;
        $this->paymentRepository    = $paymentRepository;
        $this->salesOrderRepository = $salesOrderRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $allocationPaymentModel = $builder->getData();
        $association            = $allocationPaymentModel->getAssociation();
        $user                   = $allocationPaymentModel->getUser();

        $due = $this->getDue($association, $user);

        $paymentsQueryBuilder = $this->getPaymentsQueryBuilder($association, $user);
        $payments = $paymentsQueryBuilder->getQuery()->getResult();

        $salesOrdersQueryBuilder = $this->getSalesOrdersQueryBuilder($association, $user);
        $salesOrders = $salesOrdersQueryBuilder->getQuery()->getResult();

        $newPayment = $this->paymentManager->createPayment($association, $user);
        $newPayment->setAmount($due);
        $newPayment->setType(Payment::TYPE_CHEQUE);

        $builder->add('new_payment', 'open_miam_miam_payment', array(
            'without_amount' => false,
            'with_submit'    => false,
            'property_path'  => 'payments[__new_payment__]',
            'data'           => $newPayment
        ));

        if (count($salesOrders)) {
            if (count($payments)) {
                $builder->add('payments', 'open_miam_miam_payments_for_allocate_payment', array(
                    'class'         => 'Isics\Bundle\OpenMiamMiamBundle\Entity\Payment',
                    'property'      => 'rest',
                    'expanded'      => true,
                    'multiple'      => true,
                    'required'      => false,
                    'query_builder' => $paymentsQueryBuilder,
                    'data'          => $payments
                ));
            }

            $builder->add('sales_orders', 'open_miam_miam_sales_orders_for_allocate_payment', array(
                'class'         => 'Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder',
                'property'      => 'credit',
                'multiple'      => true,
                'expanded'      => true,
                'required'      => false,
                'query_builder' => $salesOrdersQueryBuilder,
                'data'          => $salesOrders
            ));
        }
    }

    /**
     * Returns user (or anonymous) due for association
     *
     * @param Association $association
     * @param User        $user
     *
     * @return float
     */
    private function getDue(Association $association, User $user = null)
    {
        $due = 0.00;

        $subscription = $this->consumerManager->getSubscription($association, $user);

        if (null !== $subscription) {
            $due = $subscription->getLeftToPay();
        }

        return (float)$due;
    }

    /**
     * Returns payments QueryBuilder
     *
     * @param Association $association
     * @param User        $user
     *
     * @return QueryBuilder
     */
    private function getPaymentsQueryBuilder(Association $association, User $user = null)
    {
        $paymentQueryBuilder = $this->paymentRepository->createQueryBuilder('p')
            ->andWhere('p.association = :association')
            ->setParameter('association', $association)
            ->andWhere('p.rest > :minPaymentRest')
            ->setParameter('minPaymentRest', 0);

        if (null !== $user) {
            $paymentQueryBuilder->andWhere('p.user = :user')
                ->setParameter('user', $user);
        }
        else {
            $paymentQueryBuilder->andWhere('p.user IS NULL');
        }

        return $paymentQueryBuilder;
    }

    /**
     * Returns sales orders QueryBuilder
     *
     * @param Association $association
     * @param User        $user
     *
     * @return QueryBuilder
     */
    private function getSalesOrdersQueryBuilder(Association $association, User $user = null)
    {
        $salesOrdersQueryBuilder = $this->salesOrderRepository->createQueryBuilder('so')
            ->innerJoin('so.branchOccurrence', 'bo')
            ->innerJoin('bo.branch', 'b')
            ->andWhere('b.association = :association')
            ->setParameter('association', $association)
            ->andWhere('so.credit < :minSalesOrderCredit')
            ->setParameter('minSalesOrderCredit', 0);

        if (null !== $user) {
            $salesOrdersQueryBuilder->andWhere('so.user = :user')
                ->setParameter('user', $user);
        }
        else {
            $salesOrdersQueryBuilder->andWhere('so.user IS NULL');
        }

        return $salesOrdersQueryBuilder;
    }

    /**
     * @see AbstractType
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('user'));

        $resolver->setAllowedTypes(array(
            'user' => 'Isics\Bundle\OpenMiamMiamUserBundle\Entity\User'
        ));

        $resolver->setDefaults(array(
            'data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Model\Association\AllocatePayment'
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'open_miam_miam_allocate_payment';
    }
}