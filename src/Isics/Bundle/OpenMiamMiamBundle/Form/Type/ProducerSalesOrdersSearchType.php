<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 24/06/14
 * Time: 16:19
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type;


use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ProducerSalesOrdersSearchType extends AbstractType
{
    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('get')
            ->add(
                'branch',
                'entity',
                array(
                    'class'       => 'Isics\Bundle\OpenMiamMiamBundle\Entity\Branch',
                    'required'    => false,
                    'property'    => 'name',
                    'empty_value' => $this->translator->trans('admin.producer.sales_orders.list_history.filter.all_branches'),
                )
            )
            ->add(
                'minDate',
                'date',
                array(
                    'widget'   => 'single_text',
                    'format'   => 'dd-MM-yyyy',
                    'input'    => 'datetime',
                    'required' => false
                )
            )
            ->add(
                'maxDate',
                'date',
                array(
                    'widget'   => 'single_text',
                    'format'   => 'dd-MM-yyyy',
                    'input'    => 'datetime',
                    'required' => false
                )
            );
    }

    /**
     * @see AbstractType
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\ProducerSalesOrdersFilter',
            ))
            ->setRequired(['producer'])
            ->setAllowedTypes(['producer' => Producer::class]);
    }

    public function getName()
    {
        return 'open_miam_miam_producer_sales_order_search';
    }
} 