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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\DisabledException;

class SecurityController extends BaseController
{
    /**
     * {@inheritDoc}
     */
    public function loginAction(Request $request)
    {
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        if ($error) {
            if ($error instanceof DisabledException) {

                $user = $this->container->get('fos_user.user_manager')->findUserByEmail($error->getToken()->getUser());
                if (null !== $user) {
                    $this->container->get('fos_user.mailer')->sendConfirmationEmailMessage($user);
                }
            }

            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            $error = $error->getMessage();
        }
        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);

        $csrfToken = $this->container->has('form.csrf_provider')
                ? $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate')
                : null;

        return $this->renderLogin(array(
            'last_username' => $lastUsername,
            'error'         => $error,
            'csrf_token' => $csrfToken,
        ));
    }

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
