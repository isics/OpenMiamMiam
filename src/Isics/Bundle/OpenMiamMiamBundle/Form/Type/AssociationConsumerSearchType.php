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
use Symfony\Component\Translation\TranslatorInterface;

class AssociationConsumerSearchType extends AbstractType
{
    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'ref',
                'integer',
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
            ->add('creditor', 'checkbox', ['required' => false])
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