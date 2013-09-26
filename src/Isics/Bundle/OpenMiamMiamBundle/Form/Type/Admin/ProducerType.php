<?php

/*
 * This file is part of the OpenMiamMiam project.
*
* (c) Isics <contact@isics.fr>
*
* This source file is subject to the AGPL v3 license that is bundled
* with this source code in the file LICENSE.
*/

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Type\Admin;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProducerType extends AbstractType implements EventSubscriberInterface{
	
	/**
	 * 
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('name', 	'text')
				->add('imageFile', 'file', array(
						'required' => false
				))
				->add('address1', 		'text')
				->add('address2',		'text')
				->add('zipcode', 		'text')
				->add('city', 			'text')
				->add('phone1', 'text', array(
		            'required' => false
		        ))
				->add('phone2', 'text', array(
		            'required' => false
		        ))
				->add('website', 'url', array(
		            'required' => false
		        ))
				->add('facebook', 'url', array(
		            'required' => false
		        ))
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
		$producer = $event->getData();
	
		if (null !== $producer->getImage()) {
			$form->add('deleteImage', 'checkbox', array(
					'required' => false
			));
		}
	}
	

	
	/**
	 * 
	 * @param array $options
	 * @return multitype:string
	 */
	public function getDefaultOptions(array $options)
	{
		return array(
				'data_class' => 'Isics\Bundle\OpenMiamMiamBundle\Entity\Producer',
		);
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getName()
	{
		return 'open_miam_miam_admin_producer';
	}
}
