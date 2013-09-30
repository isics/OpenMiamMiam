<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddRowsToSalesOrderType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options['producer'])) {
            $producer = $options['producer'];
            $qb = function(EntityRepository $er) use ($producer) {
                return $er->getAvailableForProducerQueryBuilder($producer);
            };
        } else {
            $salesOrder = $options['salesOrder'];
            $association = $salesOrder->getBranchOccurrence()->getBranch()->getAssociation();
            $qb = function(EntityRepository $er) use ($association) {
                return $er->getAvailableForAssociationQueryBuilder($association);
            };
        }

        $builder->add(
                    'artificialProduct',
                    'open_miam_miam_artificial_product'
                )
                ->add('products', 'entity', array(
                    'class' => 'Isics\Bundle\OpenMiamMiamBundle\Entity\Product',
                    'property' => 'name',
                    'expanded' => true,
                    'multiple' => true,
                    'query_builder' => $qb
                ))
                ->add('add', 'submit');
    }

    /**
     * @see AbstractType
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('salesOrder'));
        $resolver->setOptional(array('producer'));
        $resolver->setAllowedTypes(array(
            'salesOrder' => 'Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder',
            'producer' => 'Isics\Bundle\OpenMiamMiamBundle\Entity\Producer',
        ));
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_sales_add_rows_order';
    }
}
