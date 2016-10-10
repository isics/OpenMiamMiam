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
     * @var string $termsOfServiceUrl
     */
    private $termsOfServiceUrl;

    /**
     * Constructor
     *
     * @param string $termsOfServiceUrl
     */
    public function __construct($termsOfServiceUrl)
    {
        $this->termsOfServiceUrl = $termsOfServiceUrl;
    }

    /**
     * Returns available functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('terms_of_service_url', [$this, 'getTermsOfServiceUrl']),
            new \Twig_SimpleFunction('has_terms_of_service', [$this, 'hasTermsOfService']),
        ];
    }

    /**
     * Return true if terms of service are enabled
     *
     * @return boolean
     */
    public function hasTermsOfService()
    {
        return null !== $this->getTermsOfServiceUrl();
    }

    /**
     * Returns terms of service url
     *
     * @return string
     */
    public function getTermsOfServiceUrl()
    {
        return $this->termsOfServiceUrl;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'terms_of_service_extension';
    }

}