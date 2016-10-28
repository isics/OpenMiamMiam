<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\BranchRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('mode', ChoiceType::class, array(
                'choices' => [
                    self::MODE_TURNOVER     => $this->translator->trans('admin.producer.dashboard.statistics.mode.turnover'),
                    self::MODE_SALES_ORDERS => $this->translator->trans('admin.producer.dashboard.statistics.mode.sales_orders'),
                    self::MODE_AVERAGE_CART => $this->translator->trans('admin.producer.dashboard.statistics.mode.average_cart')
                ],
                'expanded' => true,
                'multiple' => false
            ))
            ->add('branch', EntityType::class, array(
                'class'         => 'Isics\\Bundle\\OpenMiamMiamBundle\\Entity\\Branch',
                'query_builder' => function (BranchRepository $branchRepository) use ($producer) {
                    return $branchRepository->filterProducer($producer);
                },
                'choice_label'  => 'name',
                'placeholder'   => $this->translator->trans('admin.producer.dashboard.statistics.all_branches')
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(array(
                'producer'
            ))
            ->setAllowedTypes(array(
                'producer' => 'Isics\\Bundle\\OpenMiamMiamBundle\\Entity\\Producer'
            ));
    }
}
