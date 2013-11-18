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

class TermsOfServiceExtension extends \Twig_Extension
{
    /**
     * @var string $termsUrl
     */
    private $termsUrl;

    /**
     * Constructor
     *
     * @param string $termsUrl
     * 
     */
    public function __construct($termsUrl)
    {

        $this->termsUrl = $termsUrl;
    }

    /**
     * Returns available functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'terms_url' => new \Twig_Function_Method($this, 'getTermsUrl')/*,
            'has_terms_url' => new \Twig_Function_Method($this, 'hasTermsUrl')*/
        );
    }

    /**
     * Returns if terms url is null or not
     *
     * @return boolean
     */
   /* public function hasTermsUrl()
    {
        if($this->termsUrl != null){
            return true;
        }   
        else {
            return false;
        }    
    }*/

    /**
     * Returns terms url
     *
     * @return string
     */
    public function getTermsUrl()
    {
        return $this->termsUrl;
    }

}