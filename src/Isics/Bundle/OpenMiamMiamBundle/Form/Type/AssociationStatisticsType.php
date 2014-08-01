<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\BranchRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
            ->add('mode', 'choice', array(
                'choices' => [
                    self::MODE_TURNOVER     => $this->translator->trans('admin.association.dashboard.statistics.mode.turnover'),
                    self::MODE_COMMISSION   => $this->translator->trans('admin.association.dashboard.statistics.mode.commission'),
                    self::MODE_SALES_ORDERS => $this->translator->trans('admin.association.dashboard.statistics.mode.sales_orders'),
                    self::MODE_AVERAGE_CART => $this->translator->trans('admin.association.dashboard.statistics.mode.average_cart')
                ],
                'expanded' => true,
                'multiple' => false
            ))
            ->add('branch', 'entity', array(
                'class'         => 'Isics\\Bundle\\OpenMiamMiamBundle\\Entity\\Branch',
                'query_builder' => function (BranchRepository $branchRepository) use ($association) {
                    return $branchRepository->filterAssociation($association);
                },
                'property'      => 'name',
                'empty_value'   => $this->translator->trans('admin.association.dashboard.statistics.all_branches')
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array(
                'association'
            ))
            ->setAllowedTypes(array(
                'association' => 'Isics\\Bundle\\OpenMiamMiamBundle\\Entity\\Association'
            ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'open_miam_miam_association_statistics';
    }
}