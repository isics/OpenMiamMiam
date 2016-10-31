<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\BranchRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssociationStatisticsType extends AbstractType
{
    const
        MODE_TURNOVER     = 1,
        MODE_COMMISSION   = 2,
        MODE_SALES_ORDERS = 3,
        MODE_AVERAGE_CART = 4;

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
        $association = $options['association'];

        $builder
            ->add('mode', ChoiceType::class, array(
                'choices' => [
                    self::MODE_TURNOVER     => $this->translator->trans('admin.association.dashboard.statistics.mode.turnover'),
                    self::MODE_COMMISSION   => $this->translator->trans('admin.association.dashboard.statistics.mode.commission'),
                    self::MODE_SALES_ORDERS => $this->translator->trans('admin.association.dashboard.statistics.mode.sales_orders'),
                    self::MODE_AVERAGE_CART => $this->translator->trans('admin.association.dashboard.statistics.mode.average_cart')
                ],
                'expanded' => true,
                'multiple' => false
            ))
            ->add('branch', EntityType::class, array(
                'class'         => Branch::class,
                'query_builder' => function (BranchRepository $branchRepository) use ($association) {
                    return $branchRepository->filterAssociation($association);
                },
                'choice_label'  => 'name',
                'placeholder'   => $this->translator->trans('admin.association.dashboard.statistics.all_branches')
            ));
    }

    /**
     * @see AbstractType
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(array('association'))
            ->setAllowedTypes(array('association' => Association::class))
        ;
    }
}
