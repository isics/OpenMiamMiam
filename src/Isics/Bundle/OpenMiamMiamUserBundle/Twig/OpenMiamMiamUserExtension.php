<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamUserBundle\Twig;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Isics\Bundle\OpenMiamMiamUserBundle\Manager\UserManager;

class OpenMiamMiamUserExtension extends \Twig_Extension
{
    /**
     * @var UserManager $userManager
     */
    private $userManager;

    /**
     * Constructor
     *
     * @param UserManager $userManager
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Returns available functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('is_association_owner', [$this, 'isAssociationOwner']),
            new \Twig_SimpleFunction('is_producer_owner', [$this, 'isProducerOwner']),
        );
    }

    /**
     * Returns true if user is owner of association
     *
     * @param Association $association
     * @param User        $user
     *
     * @return boolean
     */
    public function isAssociationOwner(Association $association, User $user)
    {
        return $this->userManager->isOwner($association, $user);
    }

    /**
     * Returns true if user is owner of producer
     *
     * @param Producer $producer
     * @param User     $user
     *
     * @return boolean
     */
    public function isProducerOwner(Producer $producer, User $user)
    {
        return $this->userManager->isOwner($producer, $user);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'open_miam_miam_user_extension';
    }
}
