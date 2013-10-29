<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Model;

use Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;


class Mailer
{
    /**
     * @var \Swift_mailer $mailer
     */
    protected $mailer;

    /**
     * @var array $mailerConfig
     */
    protected $mailerConfig;

    /**
     * @var EngineInterface $engine
     */
    protected $engine;

    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;



    /**
     * Constructs object
     *
     * @param \Swift_Mailer       $mailer
     * @param array               $mailerConfig
     * @param EngineInterface     $engine
     * @param TranslatorInterface $translator
     */
    public function __construct(\Swift_Mailer $mailer,
                                array $mailerConfig,
                                EngineInterface $engine,
                                TranslatorInterface $translator)
    {
        $this->mailer = $mailer;
        $this->engine = $engine;
        $this->translator = $translator;

        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->mailerConfig = $resolver->resolve($mailerConfig);
    }

    /**
     * Set the defaults options
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('sender_name', 'sender_address'));
    }

    /**
     * Returns new message
     *
     * @param string $subject
     * @param string $body
     * @param string $contentType
     * @param string $charset
     *
     * @return \Swift_Message
     */
    public function getNewMessage($subject = null, $body = null, $contentType = null, $charset = null)
    {
        $mailer = \Swift_Message::newInstance($subject, $body, $contentType, $charset)
                ->setFrom(array($this->mailerConfig['sender_address'] => $this->mailerConfig['sender_name']));

        return $mailer;
    }

    /**
     * Translates text
     *
     * @param string $text
     * @param array $params
     *
     * @return string
     */
    public function translate($text, array $params = array())
    {
        return $this->translator->trans($text, $params);
    }

    /**
     * Renders template
     *
     * @param string $template
     * @param array $params
     *
     * @return string
     */
    public function render($template, array $params = array())
    {
        return $this->engine->render($template, $params);
    }

    /**
     * Sends message
     *
     * @param \Swift_Message $message
     *
     * @return int
     */
    public function send(\Swift_Message $message)
    {
        return $this->mailer->send($message);
    }
}
