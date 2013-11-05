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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductsFilterType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text')
                ->add('filter', 'submit');

        if (isset($options['association'])) {
            $association = $options['association'];
            $builder->add('producer', 'entity', array(
                'class' => 'Isics\Bundle\OpenMiamMiamBundle\Entity\Producer',
                'property' => 'name',
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'query_builder' => function(EntityRepository $er) use ($association) {
                    return $er->getForAssociationQueryBuilder($association);
                }
            ));
        }
    }

    /**
     * @see AbstractType
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('association'));
        $resolver->setAllowedTypes(array('association' => 'Isics\Bundle\OpenMiamMiamBundle\Entity\Association'));
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_product';
    }
}
