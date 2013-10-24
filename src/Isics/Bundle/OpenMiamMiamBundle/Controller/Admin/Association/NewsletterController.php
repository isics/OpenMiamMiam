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
     * Create newsletter
     * 
     * @param Request    $request
     * @param Newsletter $newsletter
     *
     * @return Response
     */
    public function createAction(Request $request, Association $association)
    {
        $newsletterManager = $this->get('open_miam_miam.newsletter_manager');
        $newsletter = $newsletterManager->createForAssociation($association);
        
        $form = $this->getForm($newsletter);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user= $this->get('security.context')->getToken()->getUser();
                $newsletterManager->save($newsletter, $user);
                $newsletterManager->sendTest($newsletter, $user, $association);
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
     *
     * @return Response
     */
    public function editAction(Request $request, Newsletter $newsletter, Association $association)
    {
        if($newsletter->getSentAt() == null)
        {
            $newsletterManager = $this->get('open_miam_miam.newsletter_manager'); 
            $user= $this->get('security.context')->getToken()->getUser();

            $form = $this->getForm($newsletter);
            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $user= $this->get('security.context')->getToken()->getUser();
                    $newsletterManager->save($newsletter, $user);
                    $newsletterManager->sendTest($newsletter, $user, $association);
                    $this->get('session')->getFlashBag()->add('notice', 'admin.association.newsletter.message.edited');

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
        else 
        {
            $this->get('session')->getFlashBag()->add('notice', 'admin.association.newsletter.message.alreadySent');

            return $this->redirect($this->generateUrl('open_miam_miam.admin.association.newsletter.create',array('id' => $newsletter->getAssociation()->getId())));
        }
        
    }
    
    /**
     * Send email to consumers or/and producers
     * 
     * @ParamConverter("newsletter", class="IsicsOpenMiamMiamBundle:Newsletter", options={"mapping": {"newsletterId": "id"}})
     * 
     * @param $newsletter
     * 
     * @return responce
     */
    public function confirmSendAction(Newsletter $newsletter)
    {
        if($newsletter->getSentAt() == null)
        {
            $user= $this->get('security.context')->getToken()->getUser();
            $newsletterManager = $this->get('open_miam_miam.newsletter_manager');
            $newsletterManager->send($newsletter);
            $newsletter->setSentAt(new \DateTime());
            $newsletterManager->save($newsletter, $user);

            $this->get('session')->getFlashBag()->add('notice', 'admin.association.newsletter.message.sent');

            return $this->redirect($this->generateUrl('open_miam_miam.admin.association.newsletter.create',array('id' => $newsletter->getAssociation()->getId())));
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'admin.association.newsletter.message.alreadySent');

            return $this->redirect($this->generateUrl('open_miam_miam.admin.association.newsletter.create',array('id' => $newsletter->getAssociation()->getId())));
        }
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