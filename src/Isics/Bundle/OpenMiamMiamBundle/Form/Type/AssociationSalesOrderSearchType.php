<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type;


use Doctrine\ORM\EntityRepository;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AssociationSalesOrderSearchType extends AbstractType
{
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
                    'multiple'      => true,
                    'expanded'      => true,
                    'class'         => 'IsicsOpenMiamMiamBundle:Branch',
                    'query_builder' =>  function(EntityRepository $er) use($association){
                            return $er->filterAssociation($association);
                        },
                    'property'      => 'name'
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