<?php

namespace KI\PonthubBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use KI\PonthubBundle\Entity\Serie;

class LoadSerieFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $serie = new Serie();
        $serie->setName('How I Met Your Mother');
        $serie->setPath('/root/web/series/How I met your mother');
        $serie->setDescription('Ted searches for the woman of his dreams in New York City with the help of his four best friends.');
        $serie->setTags([$this->getReference('tag-poseeey')]);
        $serie->setDuration(1320);
        $serie->setRating(91);
        $serie->setDirector('Carter Bays');
        $serie->setStatus('OK');
        $manager->persist($serie);
        $manager->flush();
        $this->addReference('serie-himym', $serie);
    }

    public function getOrder()
    {
        return 23;
    }
}
