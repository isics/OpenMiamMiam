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

 use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
 
 class UserExtension extends \Twig_Extension
 {

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
            'format_name_identity' => new \Twig_Function_Method($this, 'formatNameIdentity')
        );
    }

    /**
     * Return format user identity
     * 
     * @param User $user
     * @param $pattern 
     *
     * @return string
     */
    public function formatUserIdentity(User $user, $pattern = null)
    {
        return $this->formatIdentity($user->getLastname(), $user->getFirstname(), $pattern);
    }

    /**
     * Return format name identity
     * 
     * @param string $firstname
     * @param string $lastname
     * @param $pattern  
     *
     * @return string
     */
    public function formatIdentity($lastname = null, $firstname = null, $pattern = null) 
    {
        if (null === $pattern || !is_string($pattern)) {
            $pattern = '%firstname% %lastname%';
        }

        $lastname = mb_strtoupper($lastname, 'UTF-8');
        $firstname = $this->mb_ucfirst($firstname);

        return strtr($pattern, array(
            '%firstname%' => $firstname,
            '%lastname%' => $lastname
        ));
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'user_extension';
    }

    /**
     * Return format firstname
     * 
     * @param string $fristname
     *
     * @return string
     */
    private function mb_ucfirst($firstname, $encoding='UTF-8')
    {
        return mb_substr(mb_strtoupper($firstname, "utf-8"),0,1,'utf-8').mb_substr(mb_strtolower($firstname,"utf-8"),1,mb_strlen($firstname),'utf-8');
    }
}