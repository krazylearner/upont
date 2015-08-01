<?php

namespace KI\PonthubBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use KI\PonthubBundle\Entity\Software;

class LoadSoftwareFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $software = new Software();
        $software->setSize(487864000);
        $software->setPath('/root/web/softs/WVista.rar');
        $software->setName('Windows Vista');
        $software->setDescription('C\'est tout pourri mais bon...');
        $software->setTags(array($this->getReference('tag-windaube')));
        $software->setStatus('OK');
        $software->setImage($this->getReference('image-software-vista'));
        $manager->persist($software);
        $this->addReference('software-windows', $software);

        $manager->flush();
    }

    public function getOrder()
    {
        return 40;
    }
}