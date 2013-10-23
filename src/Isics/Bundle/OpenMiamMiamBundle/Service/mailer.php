<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Service;

use Symfony\Component\Templating\EngineInterface;

class Mailer {

    private $mailer;

    protected $templateEngine;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templateEngine) {
        $this->mailer = $mailer;
        $this->templateEngine = $templateEngine;
    }

    public function send($to, $subject, $view, $params = array(), $contentType = 'text/html') {
        $mail = \Swift_Message::newInstance()
        ->setSubject($subject)
        ->setFrom('your-email@example.com')
        ->setTo($to)
        ->setBody($this->templateEngine->render($view, $params), $contentType);

        $this->mailer->send( $mail );
    }
    
    public function sendTestMessage($newsletter)
    {
        
    }

}