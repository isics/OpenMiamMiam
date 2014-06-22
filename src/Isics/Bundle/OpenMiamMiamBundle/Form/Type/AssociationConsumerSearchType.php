<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 22/06/14
 * Time: 23:57
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AssociationConsumerSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'ref',
                'text',
                array(
                    'required' => false
                )
            )
            ->add(
                'lastName',
                'text',
                array(
                    'required' => false
                )
            )
            ->add(
                'firstName',
                'text',
                array(
                    'required' => false
                )
            )
            ->add(
                'creditor',
                'choice',
                array(
                    'choices' => array(
                        true => 'creditor'
                    ),
                    'required' => false,
                    'expanded' => true,
                    'multiple' => true
                )
            )
            ->add('filter', 'submit');
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_association_consumer_search';
    }
} 