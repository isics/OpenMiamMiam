<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamUserBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Isics\Bundle\OpenMiamMiamBundle\Twig\TermsOfServiceExtension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\IsTrue;

class RegistrationType extends BaseType implements EventSubscriberInterface
{

    /**
     * @var TermOfServiceExtension $termsOfServiceExtension
     */
    private $termsOfServiceExtension;

    /**
     * @param string $class The User class name
     * @param TermsOfServiceExtension $termsOfServiceExtension
     */
    public function __construct($class, TermsOfServiceExtension $termsOfServiceExtension)
    {
        parent::__construct($class);

        $this->termsOfServiceExtension = $termsOfServiceExtension;
    }

    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('firstname', 'text')
                ->add('lastname', 'text')
                ->add('address1', 'text')
                ->add('address2', 'text', array('required' => false))
                ->add('zipcode', 'text')
                ->add('city', 'text')
                ->add('phoneNumber', 'text', array('required' => false))
                ->add('isOrdersOpenNotificationSubscriber', 'checkbox', array('required'  => false))
                ->add('isNewsletterSubscriber', 'checkbox', array('required'  => false))
                ->remove('username')
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
                    new IsTrue(array('message' => 'user.register.error.terms_of_service'))
                )
            ));
        }
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_user_registration';
    }
}
