<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type;


use Doctrine\ORM\EntityRepository;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class AssociationConsumerSalesOrderSearchType extends AbstractType
{
    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * Constructor
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $association = $options['association'];

        $builder
            ->add(
                'branch',
                'entity',
                array(
                    'multiple'      => false,
                    'expanded'      => false,
                    'required'      => false,
                    'class'         => 'IsicsOpenMiamMiamBundle:Branch',
                    'query_builder' =>  function(EntityRepository $er) use($association){
                            return $er->filterAssociation($association);
                        },
                    'property'      => 'name',
                    'empty_value'   => $this->translator->trans('admin.association.consumer.orders.complete.filter.all_branches'),
                )
            )
            ->add(
                'minDate',
                'date',
                array(
                    'input'     => 'datetime',
                    'widget'    => 'single_text',
                    'required'  => false,
                    'format'    => 'dd/MM/yyyy'
                )
            )
            ->add(
                'maxDate',
                'date',
                array(
                    'input'     => 'datetime',
                    'widget'    => 'single_text',
                    'required'  => false,
                    'format'    => 'dd/MM/yyyy'
                )
            )
            ->add(
                'minTotal',
                'number',
                array(
                    'required'  => false,
                )
            )
            ->add(
                'maxTotal',
                'number',
                array(
                    'required'  => false,
                )
            )
            ->add('filter', 'submit');
    }

    /**
     * @see AbstractType
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\AssociationConsumerSalesOrdersFilter',
            ))
            ->setRequired(['association'])
            ->setAllowedTypes(['association' => Association::class]);
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_association_sales_order_search';
    }
} 