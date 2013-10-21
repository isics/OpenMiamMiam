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
use Symfony\Component\HttpFoundation\Request;

class NewsletterController
{
    /**
     * Create newsletter
     *
     * @param Request $request
     * @param Newsletter $newsletter
     *
     * @return Response
     */
    public function createAction(Request $request, Newsletter $newsletter)
    {
        
    }
    
    /**
     * Edit newsletter
     *
     * @param Request $request
     * @param Newsletter $newsletter
     *
     * @return Response
     */
    public function editAction()
    {
        
    }
    
    public function sendMail($recipientType, $subject, $body)
    {
        
    }
    
    public function sendTestMail()
    {
        
    }
    
    /**
     * Return newsletter form
     *
     * @param Newsletter $newsletter
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function getForm()
    {
        
    }

}