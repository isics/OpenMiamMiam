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

use Symfony\Component\Form\AbstractType;
use Isics\Bundle\OpenMiamMiamBundle\Twig\TermsOfServiceExtension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;

class SalesOrderConfirmationType extends AbstractType implements EventSubscriberInterface
{
    /**
     * @var TermOfServiceExtension $termsOfServiceExtension
     */
    private $termsOfServiceExtension;

    /**
     * @param TermsOfServiceExtension $termsOfServiceExtension
     */
    public function __construct(TermsOfServiceExtension $termsOfServiceExtension)
     {
         $this->termsOfServiceExtension = $termsOfServiceExtension;
     }

    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('consumerComment', 'textarea', array('required' => false))
                ->add('save', 'submit')
                ->addEventSubscriber($this);

    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();

        if($this->termsOfServiceExtension->hasTermsOfService()) {
            $form->add("termsOfService", "checkbox", array(
                'mapped' => false,
                'constraints' => array(
                    new True(array('message' => 'user.register.error.terms_of_service'))
                )
            ));
        }
    }

    /**
     * @see AbstractType
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\SalesOrderConfirmation',
        ));
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_sales_order_confirmation';
    }
}
