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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Newsletter;
use Symfony\Component\HttpFoundation\Request;

class NewsletterController extends BaseController
{
    /**
     * Create newsletter
     *
     * @param Request $request
     * @param Newsletter $newsletter
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $newsletterManager = $this->get('open_miam_miam.newsletter_manager');
        $newsletter = $newsletterManager->createForAssociation($association);
        
        $form = $this->getForm($newsletter);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user= $this->get('security.context')->getToken()->getUser();
                $newsletterManager->save($newsletter);
                $newsletterManager->sendTest($newsletter, $user);
                $this->get('session')->getFlashBag()->add('notice', 'admin.association.newsletter.message.created');
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
     * @param Request $request
     * @param Newsletter $newsletter
     *
     * @return Response
     */
    public function editAction(Newsletter $newsletter)
    {
        $newsletterManager = $this->get('open_miam_miam.newsletter_manager'); 
        
        $form = $this->getForm($newsletter);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $newsletterManager->save($newsletter);
                $this->send($newsletter);
                $this->get('session')->getFlashBag()->add('notice', 'admin.association.newsletter.message.created');
            }
        }
        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Newsletter:create.html.twig', array(
                'association' => $association,
                'form'        => $form->createView(),
        ));
    }
    
    public function send($newsletter)
    {
        if($newsletter->getSentAt() != null)
        {
            newsletterManager->send($newsletter);
            $newsletter->setSentAt(new \DateTime())
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