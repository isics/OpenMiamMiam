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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProductsFilterType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class)
                ->add('filter', SubmitType::class);

        if (isset($options['association'])) {
            $association = $options['association'];
            $builder->add('producer', EntityType::class, array(
                'class' => 'Isics\Bundle\OpenMiamMiamBundle\Entity\Producer',
                'choice_label' => 'name',
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setOptional(array('association'));
        $resolver->setAllowedTypes('association', Association::class);
    }
}
