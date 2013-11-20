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

use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;

class ProfileType extends BaseType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('firstname', 'text', array('required' => false))
                ->add('lastname', 'text')
                ->add('address1', 'text')
                ->add('address2', 'text', array('required' => false))
                ->add('zipcode', 'text')
                ->add('city', 'text')
                ->add('phoneNumber', 'text', array('required' => false))
                ->add('isOrdersOpenNotificationSubscriber', 'checkbox', array('required'  => false))
                ->add('isNewsletterSubscriber', 'checkbox', array('required'  => false))
                ->remove('current_password')
                ->remove('username')
                ->remove('email');
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_user_profile';
    }
}
