<?php

namespace App\DataFixtures;

use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ImageFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $images = [
            'user-archlinux' => 'archlinux.png',
            'user-trezzinl' => 'trezzinl.jpg',
            'user-bochetc' => 'bochetc.jpg',
            'user-de-boisc' => 'de-boisc.jpg',
            'user-trancara' => 'trancara.jpg',
            'user-dziris' => 'dziris.jpg',
            'user-kadaouic' => 'kadaouic.jpg',
            'user-guerinh' => 'guerinh.jpg',
            'user-muzardt' => 'muzardt.jpg',
            'user-taquet-c' => 'taquet-c.jpg',
            'user-admissibles' => 'admissibles.png',
            'user-gcc' => 'gcc.jpg',
            'club-bda' => 'bda.jpg',
            'club-bde' => 'bde.jpg',
            'club-foyer' => 'foyer.jpg',
            'club-ki' => 'ki.png',
            'club-pep' => 'pep.png',
            'club-bpc' => 'bpc.png',
            'club-pma' => 'pma.jpeg',
            'newsitem-git' => 'git.png',
            'newsitem-pulls' => 'pulls.jpg',
            'movie-pumping-iron' => 'pumping-iron.jpg',
            'game-age-of-empires-2' => 'age-of-empires-2.jpg',
            'software-vista' => 'vista.png',
            'supaero' => 'supaero.jpg'
        ];

        $path = __DIR__ . '/../../tests/uploads/';
        $fs = new Filesystem();

        foreach ($images as $tag => $name) {
            $fs->copy($path . $name, $path . 'tmp_' . $name);
            $file = new File($path . 'tmp_' . $name);
            $image = new Image();
            $image->setFile($file);
            $image->setExt($file->getExtension());
            $manager->persist($image);
            $this->addReference('image-' . $tag, $image);
        }
        $manager->flush();
    }
}
