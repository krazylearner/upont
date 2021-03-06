<?php

namespace KI\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use KI\CoreBundle\Entity\Comment;

class LoadCommentFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $comment = new Comment();
        $comment->setDate(time());
        $comment->setText('Génial !');
        $comment->setAuthor($this->getReference('user-trancara'));
        $comment->addLike($this->getReference('user-trancara'));
        $comment->addLike($this->getReference('user-dziris'));
        $comment->addDislike($this->getReference('user-de-boisc'));
        $manager->persist($comment);
        $this->addReference('comment-genial', $comment);

        $comment = new Comment();
        $comment->setDate(time() + 3);
        $comment->setText('Non mais là ca va pas du tout, c\'est de la merde pure votre truc. Je me casse.
Such rage.');
        $comment->setAuthor($this->getReference('user-de-boisc'));
        $comment->addDislike($this->getReference('user-trancara'));
        $manager->persist($comment);
        $this->addReference('comment-rage', $comment);

        $comment = new Comment();
        $comment->setDate(time() + 6);
        $comment->setText('Je vous demande de vous arrêter.');
        $comment->setAuthor($this->getReference('user-guerinh'));
        $manager->persist($comment);
        $this->addReference('comment-arret', $comment);

        $manager->flush();
    }

    public function getOrder()
    {
        return 4;
    }
}
