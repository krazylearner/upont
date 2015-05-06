<?php

namespace KI\UpontBundle\DataFixtures\ORM\Publications;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use KI\UpontBundle\Entity\Publications\Tuto;

class LoadTutoFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $tuto = new Tuto();
        $tuto->setName('Réglages Internet');
        $tuto->setText('Pour régler le proxy sous Windows, allez dans "Options Internet" (cf Panneau de configuration/barre de recherche du menu démarrer pour Windows Vista+), onglet "Connexions", bouton "Paramètres réseau". Il suffit de régler le proxy : (etu)proxy.enpc.fr -- port 3128.');
        $tuto->setDate(1414242424);
        $tuto->setCategory('Réseau');
        $manager->persist($tuto);

        $tuto = new Tuto();
        $tuto->setName('Filtrer vos mails');
        $tuto->setText('Sous Zimbra : aller dans l\'onglet "Préférences", et là vous avez un onglet "Filtres". Créer un filtre est très facile !');
        $tuto->setDate(1418325122);
        $tuto->setCategory('Réglages logiciel');
        $manager->persist($tuto);

        $tuto = new Tuto();
        $tuto->setName('Comment créer un club');
        $tuto->setText('Pour ceux ou celles qui auraient envie de créer un club, voici une page expliquant ce qu\'il faut faire !');
        $tuto->setDate(1412831521);
        $tuto->setCategory('uPont');
        $manager->persist($tuto);

        $manager->flush();
    }

    public function getOrder()
    {
        return 25;
    }
}
