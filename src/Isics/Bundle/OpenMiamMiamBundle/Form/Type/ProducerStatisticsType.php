<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\BranchRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ProducerStatisticsType extends AbstractType
{
    const
        MODE_TURNOVER     = 1,
        MODE_SALES_ORDERS = 2,
        MODE_AVERAGE_CART = 3;

    /**
     * @var TranslatorInterface
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
     * @see AbstractType::buildForm
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $producer = $options['producer'];

        $builder
            ->add('mode', 'choice', array(
                'choices' => [
                    self::MODE_TURNOVER     => $this->translator->trans('admin.producer.dashboard.statistics.mode.turnover'),
                    self::MODE_SALES_ORDERS => $this->translator->trans('admin.producer.dashboard.statistics.mode.sales_orders'),
                    self::MODE_AVERAGE_CART => $this->translator->trans('admin.producer.dashboard.statistics.mode.average_cart')
                ],
                'expanded' => true,
                'multiple' => false
            ))
            ->add('branch', 'entity', array(
                'class'         => 'Isics\\Bundle\\OpenMiamMiamBundle\\Entity\\Branch',
                'query_builder' => function (BranchRepository $branchRepository) use ($producer) {
                    return $branchRepository->filterProducer($producer);
                },
                'property'      => 'name',
                'empty_value'   => $this->translator->trans('admin.producer.dashboard.statistics.all_branches')
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array(
                'producer'
            ))
            ->setAllowedTypes(array(
                'producer' => 'Isics\\Bundle\\OpenMiamMiamBundle\\Entity\\Producer'
            ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'open_miam_miam_producer_statistics';
    }
}