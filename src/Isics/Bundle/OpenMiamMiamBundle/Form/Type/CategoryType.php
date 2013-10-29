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
use Isics\Bundle\OpenMiamMiamBundle\Model\Category\CategoryNode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CategoryType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');

        if (!$options['root_node']) {
            $builder->add('position', 'choice', array(
                'choices' => array(
                    CategoryNode::POSITION_FIRST_CHILD_OF  => 'tree.position.first_child_of',
                    CategoryNode::POSITION_LAST_CHILD_OF   => 'tree.position.last_child_of',
                    CategoryNode::POSITION_PREV_SIBLING_OF => 'tree.position.prev_sibling_of',
                    CategoryNode::POSITION_NEXT_SIBLING_OF => 'tree.position.next_sibling_of',
                )
            ))
            ->add('target', 'entity', array(
                'class'         => 'IsicsOpenMiamMiamBundle:Category',
                'property'      => 'indentedName',
                'query_builder' => function(EntityRepository $er) {
                    return $er->getNodesHierarchyQueryBuilder();
                },
                'required'      => false,
            ));
        }

        $builder->add('save', 'submit');
    }

    /**
     * @see AbstractType
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Model\Category\CategoryNode',
            'root_node'  => false,
        ));
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_category';
    }
}
