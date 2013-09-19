<?php
/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Isics\Bundle\OpenMiamMiamUserBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as BaseController;

class SecurityController extends BaseController
{
    /**
     * {@inheritDoc}
     */
    protected function renderLogin(array $data)
    {
        $requestAttributes = $this->container->get('request')->attributes;
        if ($requestAttributes->get('_route') == 'open_miam_miam_user.admin.security.login') {
            $template = 'IsicsOpenMiamMiamUserBundle:Security:adminLogin.html.twig';
        } else {
            $template = 'FOSUserBundle:Security:login.html.twig';
        }

        return $this->container->get('templating')->renderResponse($template, $data);
    }
}
