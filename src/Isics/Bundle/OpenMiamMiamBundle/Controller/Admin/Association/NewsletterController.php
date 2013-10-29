<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Association;

use Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Association\BaseController;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Newsletter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class NewsletterController extends BaseController
{
    /**
     * Secures article for association
     *
     * @param Association $association
     * @param Newsletter  $newsletter
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function secureNewsletter(Association $association, Newsletter $newsletter)
    {
        if ($association->getId() !== $newsletter->getAssociation()->getId()) {
            throw $this->createNotFoundException('Invalid newsletter for association');
        }
    }

    /**
     * Create newsletter
     *
     * @param Request     $request
     * @param Association $association
     *
     * @return Response
     */
    public function createAction(Request $request, Association $association)
    {
        $this->secure($association);

        $newsletterManager = $this->get('open_miam_miam.newsletter_manager');
        $newsletter = $newsletterManager->createForAssociation($association);

        $form = $this->getForm($newsletter);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user = $this->get('security.context')->getToken()->getUser();
                $newsletterManager->saveAndSendTest($newsletter, $user);
                $this->get('session')->getFlashBag()->add('notice', 'admin.association.newsletter.message.created');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.association.newsletter.edit',
                    array('id' => $association->getId(), 'newsletterId' => $newsletter->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Newsletter:create.html.twig', array(
            'association' => $association,
            'form'        => $form->createView(),
        ));
    }

    /**
     * Edit newsletter
     *
     * @ParamConverter("newsletter", class="IsicsOpenMiamMiamBundle:Newsletter", options={"mapping": {"newsletterId": "id"}})
     *
     * @param Request    $request
     * @param Newsletter $newsletter
     * @param Assocation $association
     *
     * @return Response
     */
    public function editAction(Request $request, Association $association, Newsletter $newsletter)
    {
        $this->secure($association);
        $this->secureNewsletter($association, $newsletter);

        if ($newsletter->getSentAt() === null) {
            $newsletterManager = $this->get('open_miam_miam.newsletter_manager');
            $user= $this->get('security.context')->getToken()->getUser();

            $form = $this->getForm($newsletter);
            if ($request->isMethod('POST')) {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $user = $this->get('security.context')->getToken()->getUser();
                    $newsletterManager->saveAndSendTest($newsletter, $user);
                    $this->get('session')->getFlashBag()->add('notice', 'admin.association.newsletter.message.updated');

                    return $this->redirect($this->generateUrl(
                        'open_miam_miam.admin.association.newsletter.edit',
                        array('id' => $association->getId(), 'newsletterId' => $newsletter->getId())
                    ));
                }
            }
            return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Newsletter:edit.html.twig', array(
                'association' => $association,
                'form'        => $form->createView(),
                'activities'  => $newsletterManager->getActivities($newsletter),
            ));
        }
        else {
            $this->get('session')->getFlashBag()->add('notice', 'admin.association.newsletter.message.already_sent');

            return $this->redirect($this->generateUrl('open_miam_miam.admin.association.newsletter.create', array('id' => $association->getId())));
        }

    }

    /**
     * Send email to consumers or/and producers
     *
     * @ParamConverter("newsletter", class="IsicsOpenMiamMiamBundle:Newsletter", options={"mapping": {"newsletterId": "id"}})
     *
     * @param Association $association
     * @param Newsletter  $newsletter
     *
     * @return response
     */
    public function sendAction(Association $association, Newsletter $newsletter)
    {
        $this->secure($association);
        $this->secureNewsletter($association, $newsletter);

        if ($newsletter->getSentAt() === null) {
            $user = $this->get('security.context')->getToken()->getUser();
            $newsletterManager = $this->get('open_miam_miam.newsletter_manager');
            $newsletterManager->send($newsletter, $user);

            $this->get('session')->getFlashBag()->add('notice', 'admin.association.newsletter.message.sent');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'admin.association.newsletter.message.already_sent');
        }

        return $this->redirect(
            $this->generateUrl('open_miam_miam.admin.association.newsletter.report', array(
                'id' => $association->getId(),
                'newsletterId' => $newsletter->getId(),
            ))
        );
    }

    /**
     * Show newsletter report
     *
     * @ParamConverter("newsletter", class="IsicsOpenMiamMiamBundle:Newsletter", options={"mapping": {"newsletterId": "id"}})
     *
     * @param Newsletter $newsletter
     * @param Assocation $association
     *
     * @return Response
     */
    public function showReportAction(Association $association, Newsletter $newsletter)
    {
        $this->secure($association);
        $this->secureNewsletter($association, $newsletter);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\association\Newsletter:showReport.html.twig', array(
            'newsletter' => $newsletter,
            'activities' => $this->get('open_miam_miam.newsletter_manager')->getActivities($newsletter),
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
                'open_miam_miam.admin.association.newsletter.create',
                array('id' => $newsletter->getAssociation()->getId())
            );
        } else {
            $action = $this->generateUrl(
                'open_miam_miam.admin.association.newsletter.edit',
                array('id' => $newsletter->getAssociation()->getId(), 'newsletterId' => $newsletter->getId())
            );
        }

        return $this->createForm(
            $this->get('open_miam_miam.form.type.association_newsletter'),
            $newsletter,
            array('action' => $action, 'method' => 'POST')
        );
    }
}