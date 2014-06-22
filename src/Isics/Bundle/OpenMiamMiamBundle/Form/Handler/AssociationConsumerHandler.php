<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Form\Handler;


use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Symfony\Component\Form\FormFactoryInterface;

class AssociationConsumerHandler
{
    /**
     * @var FormFactoryInterface $formFactory
     */
    protected $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Returns a form used to apply filters to a consumers list
     *
     * @param Association $association
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createSearchForm()
    {
        return $this->formFactory->create('open_miam_miam_association_consumer_search');
    }
} 