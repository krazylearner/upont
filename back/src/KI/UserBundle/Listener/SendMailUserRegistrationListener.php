<?php

namespace KI\UserBundle\Listener;

use KI\UserBundle\Entity\User;
use KI\UserBundle\Event\UserRegistrationEvent;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class SendMailUserRegistrationListener
{
    private $swiftMailer;
    private $templatingEngine;

    public function __construct(Swift_Mailer $swiftMailer, EngineInterface $templatingEngine)
    {
        $this->swiftMailer = $swiftMailer;
        $this->templatingEngine = $templatingEngine;
    }

    // Check si un achievement donné est accompli, si oui envoie une notification
    public function sendMail(UserRegistrationEvent $event)
    {
        $attributes = $event->getAttributes();
        $email = $event->getUser()->getEmail();
        $username = $event->getUser()->getUsername();

        // Envoi du mail
        $message = (new Swift_Message('Inscription uPont'))
            ->setFrom('noreply@upont.enpc.fr')
            ->setTo($email)
            ->setBody($this->templatingEngine->render('KIUserBundle::registration.txt.twig', $attributes));

        $this->swiftMailer->send($message);

        $message = (new Swift_Message('[uPont] Nouvelle inscription (' . $username . ')'))
            ->setFrom('noreply@upont.enpc.fr')
            ->setTo('upont@clubinfo.enpc.fr')
            ->setBody($this->templatingEngine->render('KIUserBundle::registration-ki.txt.twig', $attributes));

        $this->swiftMailer->send($message);
    }
}
