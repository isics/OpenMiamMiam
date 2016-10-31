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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Isics\Bundle\OpenMiamMiamBundle\Model\Category\CategoryNode;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class);

        if (!$options['data']->isRoot()) {
            $builder->add('position', ChoiceType::class, array(
                'choices' => array(
                    CategoryNode::POSITION_FIRST_CHILD_OF  => 'tree.position.first_child_of',
                    CategoryNode::POSITION_LAST_CHILD_OF   => 'tree.position.last_child_of',
                    CategoryNode::POSITION_PREV_SIBLING_OF => 'tree.position.prev_sibling_of',
                    CategoryNode::POSITION_NEXT_SIBLING_OF => 'tree.position.next_sibling_of',
                )
            ))
            ->add('target', EntityType::class, array(
                'class'         => Category::class,
                'choice_label'  => 'indentedName',
                'query_builder' => function(EntityRepository $er) {
                    return $er->getNodesHierarchyQueryBuilder();
                },
                'required'      => false,
            ));
        }

        $builder->add('save', SubmitType::class);
    }

    /**
     * @see AbstractType
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => CategoryNode::class));
    }
}
