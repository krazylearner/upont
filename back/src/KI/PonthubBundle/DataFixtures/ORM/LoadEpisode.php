<?php

namespace KI\PonthubBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use KI\PonthubBundle\Entity\Episode;

class LoadEpisodeFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $episode = new Episode();
        $episode->setPath('/root/web/series/How I met your mother/Saison 1/S01 E01 - Pilot.avi');
        $episode->setName('Pilot');
        $episode->setSeason(1);
        $episode->setNumber(1);
        $episode->setSize(700*1000*1000);
        $episode->setSerie($this->getReference('serie-himym'));
        $episode->setStatus('OK');
        $manager->persist($episode);
        $this->addReference('episode-pilot', $episode);

        $manager->flush();
    }

    public function getOrder()
    {
        return 24;
    }
}
