<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 24/06/14
 * Time: 16:19
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProducerSalesOrdersSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('get')
            ->add(
                'branchOccurrence',
                'entity',
                array(
                    'required' => false,
                    'multiple' => false,
                    'extended' => false,
                )
            )
            ->add(
                'minDate',
                'date',
                array(
                    'widget'   => 'single_text',
                    'format'   => 'dd-MM-yyyy',
                    'input'    => 'datetime',
                    'required' => false,
                    'multiple' => false,
                    'extended' => false,
                )
            )
            ->add(
                'maxDate',
                'date',
                array(
                    'widget'   => 'single_text',
                    'format'   => 'dd-MM-yyyy',
                    'input'    => 'datetime',
                    'required' => false,
                    'multiple' => false,
                    'extended' => false,
                )
            )
            ->add(
                'minTotal',
                'money',
                array(
                    'required' => false,
                )
            )
            ->add(
                'maxTotal',
                'money',
                array(
                    'required' => false,
                )
            );
    }

    public function getDefaultOptions(array $options)
    {
        return array('data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\ProducerSalesOrdersFilter');
    }

    public function getName()
    {
        return 'open_miam_miam_producer_sales_order_search';
    }
} 