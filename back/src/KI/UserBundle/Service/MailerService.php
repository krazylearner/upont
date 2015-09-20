<?php

namespace KI\UserBundle\Service;

use KI\UserBundle\Entity\User;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\TwigBundle\TwigEngine;

class MailerService
{
    protected $mailer;
    protected $templating;

    public function __construct(Swift_Mailer $mailer, TwigEngine $templating)
    {
        $this->mailer     = $mailer;
        $this->templating = $templating;
    }

    public function send(User $from, array $to, $title, $template, $vars)
    {
        $message = Swift_Message::newInstance()
            ->setSubject($title)
            ->setFrom('evenements@upont.enpc.fr')
            ->setReplyTo(array($from->getEmail() => $from->getFirstName().' '.$from->getLastName()))
            ->setBody($this->templating->render($template, $vars), 'text/html')
        ;

        foreach ($to as $user) {
            $message->setTo(array($user->getEmail() => $user->getFirstName().' '.$user->getLastName()));
            $this->mailer->send($message);
        }
    }
}