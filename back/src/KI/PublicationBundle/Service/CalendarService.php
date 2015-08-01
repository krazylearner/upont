<?php

namespace KI\PublicationBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use KI\UserBundle\Entity\Users\User;
use KI\UserBundle\Entity\Achievement;
use KI\UserBundle\Event\AchievementCheckEvent;

//Service permettant de gérer les calendrier
class CalendarService extends ContainerAware
{
    protected $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    private function toDateTime($timestamp)
    {
        $date = new \DateTime();
        $date->setTimestamp($timestamp);
        return $date;
    }

    // Retourne un calendrier au format ICS
    public function getCalendar(User $user, array $events)
    {
        $provider = $this->container->get('bomo_ical.ics_provider');

        //On se positionne à Paris
        $tz = $provider->createTimezone();
        $tz->setTzid('Europe/Paris')->setProperty('X-LIC-LOCATION', $tz->getTzid());

        //Titre et description
        $cal = $provider->createCalendar($tz);
        $cal->setName('Calendrier uPont')
            ->setDescription('Calendrier ICS des évènements uPont');

        foreach ($events as $eventDb) {
            $event = $cal->newEvent();
            $event
                ->setStartDate($this->toDateTime($eventDb->getStartDate()))
                ->setEndDate($this->toDateTime($eventDb->getEndDate()))
                ->setName($eventDb->getName())
                ->setDescription($eventDb->getText())
                ->setLocation($eventDb->getPlace());
        }

        $dispatcher = $this->container->get('event_dispatcher');
        $achievementCheck = new AchievementCheckEvent(Achievement::ICS_CALENDAR, $user);
        $dispatcher->dispatch('upont.achievement', $achievementCheck);

        return $cal->returnCalendar();
    }
}
