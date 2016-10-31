<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 24/06/14
 * Time: 16:19
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\ProducerSalesOrdersFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('branch', EntityType::class, array(
                'class'        => Branch::class,
                'required'     => false,
                'choice_label' => 'name',
                'placeholder'  => $this->translator->trans('admin.producer.sales_orders.list_history.filter.all_branches'),
            ))
            ->add('minDate', 'date', array(
                'widget'   => 'single_text',
                'format'   => 'dd/MM/yyyy',
                'input'    => 'datetime',
                'required' => false
            ))
            ->add('maxDate', 'date', array(
                'widget'   => 'single_text',
                'format'   => 'dd/MM/yyyy',
                'input'    => 'datetime',
                'required' => false
            ));
    }

    /**
     * @see AbstractType
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array('data_class' => ProducerSalesOrdersFilter::class))
            ->setRequired(array('producer'))
            ->setAllowedTypes(array('producer' => Producer::class));
    }
}
