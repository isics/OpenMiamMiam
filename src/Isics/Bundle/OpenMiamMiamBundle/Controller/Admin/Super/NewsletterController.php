<?php

/*
 * This file is part of the OpenMiamMiam project.
*
* (c) Isics <contact@isics.fr>
*
* This source file is subject to the AGPL v3 license that is bundled
* with this source code in the file LICENSE.
*/

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Super;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Newsletter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class NewsletterController extends Controller
{
    /**
     * Create newsletter
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $newsletterManager = $this->get('open_miam_miam.newsletter_manager');
        $newsletter = $newsletterManager->createForSuper();

        $form = $this->getForm($newsletter);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user = $this->get('security.context')->getToken()->getUser();
                $newsletterManager->saveAndSendTest($newsletter, $user);
                $this->get('session')->getFlashBag()->add('notice', 'admin.super.newsletter.message.created');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.super.newsletter.edit',
                    array('newsletterId' => $newsletter->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Newsletter:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Edit newsletter
     *
     * @ParamConverter("newsletter", class="IsicsOpenMiamMiamBundle:Newsletter", options={"mapping": {"newsletterId": "id"}})
     *
     * @param Request    $request
     * @param Newsletter $newsletter
     *
     * @return Response
     */
    public function editAction(Request $request, Newsletter $newsletter)
    {
        if ($newsletter->getSentAt() === null) {
            $newsletterManager = $this->get('open_miam_miam.newsletter_manager');

            $form = $this->getForm($newsletter);
            if ($request->isMethod('POST')) {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $user = $this->get('security.context')->getToken()->getUser();
                    $newsletterManager->saveAndSendTest($newsletter, $user);
                    $this->get('session')->getFlashBag()->add('notice', 'admin.super.newsletter.message.updated');

                    return $this->redirect($this->generateUrl(
                        'open_miam_miam.admin.super.newsletter.edit',
                        array('newsletterId' => $newsletter->getId())
                    ));
                }
            }
            return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Newsletter:edit.html.twig', array(
                'form'       => $form->createView(),
                'activities' => $newsletterManager->getActivities($newsletter),
            ));
        } else {
            $this->get('session')->getFlashBag()->add('notice', 'admin.super.newsletter.message.already_sent');

            return $this->redirect($this->generateUrl('open_miam_miam.admin.super.newsletter.create'));
        }
    }

    /**
     * Send email to consumers or/and producers
     *
     * @ParamConverter("newsletter", class="IsicsOpenMiamMiamBundle:Newsletter", options={"mapping": {"newsletterId": "id"}})
     *
     * @param Newsletter $newsletter
     *
     * @return response
     */
    public function sendAction(Newsletter $newsletter)
    {
        if ($newsletter->getSentAt() === null) {
            $user = $this->get('security.context')->getToken()->getUser();
            $newsletterManager = $this->get('open_miam_miam.newsletter_manager');
            $newsletterManager->send($newsletter, $user);

            $this->get('session')->getFlashBag()->add('notice', 'admin.super.newsletter.message.sent');

        } else {
            $this->get('session')->getFlashBag()->add('error', 'admin.super.newsletter.message.already_sent');
        }

        return $this->redirect($this->generateUrl('open_miam_miam.admin.super.newsletter.report',
            array('newsletterId' => $newsletter->getId())));
    }

    /**
     * Show newsletter send
     * 
     * @ParamConverter("newsletter", class="IsicsOpenMiamMiamBundle:Newsletter", options={"mapping": {"newsletterId": "id"}})
     * 
     * @return response
     */
    public function showReportAction(Newsletter $newsletter)
    {
        $newsletters = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Newsletter')->findForSuper();
        $newsletterManager = $this->get('open_miam_miam.newsletter_manager');
        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Newsletter:showReport.html.twig', array(
            'newsletter' => $newsletter,
            'activities' => $newsletterManager->getActivities($newsletter),
        ));
    }

    /**
     * Return newsletter form
     *
     * @param Newsletter $newsletter
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function getForm(Newsletter $newsletter)
    {
        if (null === $newsletter->getId()) {
            $action = $this->generateUrl(
                'open_miam_miam.admin.super.newsletter.create'
            );
        } else {
            $action = $this->generateUrl(
                'open_miam_miam.admin.super.newsletter.edit',
                array('newsletterId' => $newsletter->getId())
            );
        }

        return $this->createForm(
            $this->get('open_miam_miam.form.type.super_newsletter'),
            $newsletter,
            array('action' => $action, 'method' => 'POST')
        );
    }
}