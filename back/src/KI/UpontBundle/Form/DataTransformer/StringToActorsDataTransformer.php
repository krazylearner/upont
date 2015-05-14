<?php
namespace KI\UpontBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use KI\UpontBundle\Entity\Ponthub\Actor;

class StringToActorsDataTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    // En théorie, ne sera jamais utilisé
    public function transform($actors)
    {
        if (null === $actors)
            return '';

        return '';
    }

    public function reverseTransform($string)
    {
        if (!$string)
            return null;

        $array = new \Doctrine\Common\Collections\ArrayCollection();
        $repo = $this->om->getRepository('KIUpontBundle:Ponthub\Actor');
        foreach (explode(',', $string) as $actor) {
            $item = $repo->findOneByName($actor);

            if ($item instanceof Actor) {
                $array->add($item);
            } else {
                $actorItem = new Actor();
                $actorItem->setName($actor);
                $this->om->persist($actorItem);
                $array->add($actorItem);
            }
        }
        $this->om->flush();

        return $array;
    }
}