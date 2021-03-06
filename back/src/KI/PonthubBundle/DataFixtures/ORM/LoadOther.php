<?php

namespace KI\PonthubBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use KI\PonthubBundle\Entity\Other;

class LoadOtherFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $other = new Other();
        $other->setSize(1024*1024*1024);
        $other->setPath('/root/web/autres/windows vista.iso');
        $other->setName('Windows Vista');
        $other->setDescription('Aimez-vous vraiment l\'informatique?');
        $other->setTags([$this->getReference('tag-windaube'), $this->getReference('tag-merde'), $this->getReference('tag-daube')]);
        $other->setStatus('OK');
        $manager->persist($other);
        $this->addReference('other-windows-vista', $other);

        $manager->flush();
    }

    public function getOrder()
    {
        return 29;
    }
}
