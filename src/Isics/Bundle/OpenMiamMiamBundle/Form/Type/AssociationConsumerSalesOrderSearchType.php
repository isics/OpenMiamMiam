<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type;


use Doctrine\ORM\EntityRepository;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\AssociationConsumerSalesOrdersFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->setMethod('GET')
            ->add('ref', TextType::class, array('required' => false))
            ->add('branch', EntityType::class, array(
                'multiple'      => false,
                'expanded'      => false,
                'required'      => false,
                'class'         => Branch::class,
                'query_builder' =>  function(EntityRepository $er) use($association) {
                        return $er->filterAssociation($association);
                    },
                'choice_label'  => 'name',
                'placeholder'   => $this->translator->trans('admin.association.consumer.orders.complete.filter.all_branches'),
            ))
            ->add('minDate', DateType::class, array(
                'input'     => 'datetime',
                'widget'    => 'single_text',
                'required'  => false,
                'format'    => 'dd/MM/yyyy'
            ))
            ->add('maxDate', DateType::class, array(
                'input'     => 'datetime',
                'widget'    => 'single_text',
                'required'  => false,
                'format'    => 'dd/MM/yyyy'
            ))
            ->add('minTotal', NumberType::class, array('required'  => false))
            ->add('maxTotal', NumberType::class, array('required'  => false));
    }

    /**
     * @see AbstractType
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array('data_class' => AssociationConsumerSalesOrdersFilter::class))
            ->setRequired(array('association'))
            ->setAllowedTypes('association', Association::class);
    }
}
