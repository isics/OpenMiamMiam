<?php
/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */
 
namespace Isics\Bundle\OpenMiamMiamBundle\Twig;

use Isics\Bundle\OpenMiamMiamBundle\Formatter\UserFormatter;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
 
class UserExtension extends \Twig_Extension
{
    /**
     * @var UserFormatter
     */
    protected $userFormatter;

    /**
     * @param UserFormatter $userFormatter User formatter
     */
    public function __construct(UserFormatter $userFormatter)
    {
        $this->userFormatter = $userFormatter;
    }

    /**
     * Returns available functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'format_user_identity' => new \Twig_Function_Method($this, 'formatUserIdentity'),
            'format_identity' => new \Twig_Function_Method($this, 'formatIdentity'),
        );
    }

    /**
     * Return format user identity
     * 
     * @param User    $user
     * @param $string $pattern 
     *
     * @return array
     */
    public function formatUserIdentity(User $user, $pattern = null)
    {
        return $this->userFormatter->formatUserIdentity($user, $pattern);
    }

    /**
     * Return format identity
     * 
     * @param string $firstname_first_letter
     * @param string $lastname
     * @param string $pattern  
     *
     * @return array
     */
    public function formatIdentity($lastname = null, $firstname = null, $pattern = null) 
    {
        return $this->userFormatter->formatIdentity($lastname, $firstname, $pattern);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'user_extension';
    }
}