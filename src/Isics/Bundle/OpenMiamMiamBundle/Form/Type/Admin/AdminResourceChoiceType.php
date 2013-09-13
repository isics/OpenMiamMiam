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

use Isics\Bundle\OpenMiamMiamBundle\Model\Admin\AdminResourceCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AdminResourceChoiceType extends AbstractType
{
    /**
     * @var AdminResourceCollection $adminResources
     */
    protected $adminResourceCollection;



    /**
     * Constructs object
     *
     * @param AdminResourceCollection $adminResourceCollection
     */
    public function __construct(AdminResourceCollection $adminResourceCollection)
    {
        $this->adminResourceCollection = $adminResourceCollection;
    }

    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('admin', 'choice', array('choices' => $this->adminResourceCollection->getElements()));
    }

    /**
     * @see AbstractType
     */
    public function getName()
    {
        return 'open_miam_miam_admin_resource_choice';
    }
}
