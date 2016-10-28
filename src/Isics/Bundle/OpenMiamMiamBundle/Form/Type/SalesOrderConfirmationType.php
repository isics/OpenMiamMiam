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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $builder->add('consumerComment', TextareaType::class, array('required' => false))
                ->add('save', SubmitType::class)
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
            $form->add("termsOfService", CheckboxType::class, array(
                'mapped' => false,
                'constraints' => array(
                    new IsTrue(array('message' => 'order.confirm.error.terms_of_service'))
                )
            ));
        }
    }

    /**
     * @see AbstractType
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Model\SalesOrder\SalesOrderConfirmation',
        ));
    }
}
