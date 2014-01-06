<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Formatter;

use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;

class UserFormatter
{
    /**
     * @var string $pattern
     */
    private $pattern;

    /**
     * Constructor
     *
     * @param string $pattern
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
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
        return $this->formatIdentity($user->getLastname(), $user->getFirstname(), $pattern);
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
        if (null === $pattern || !is_string($pattern)) {
            $pattern = $this->pattern;
        }

        $lastname = mb_strtoupper($lastname, 'UTF-8');
        $firstname = $this->mb_ucfirst($firstname);

        return strtr($pattern, array(
            '%firstname%' => $firstname,
            '%lastname%' => $lastname,
            '%firstname_first_letter%' => mb_substr($firstname, 0, 1, 'UTF-8')
        ));
    }

    /**
     * Return string with capitalized first letter
     *
     * @param string $string
     * @param string $encoding
     *
     * @return string
     */
    private function mb_ucfirst($string, $encoding = 'UTF-8')
    {
        return mb_substr(mb_strtoupper($string, $encoding), 0, 1, $encoding).mb_substr(mb_strtolower($string, $encoding), 1, mb_strlen($string), $encoding);
    }
}