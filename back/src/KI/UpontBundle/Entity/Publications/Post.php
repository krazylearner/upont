<?php

namespace KI\UpontBundle\Entity\Publications;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use KI\UpontBundle\Entity\Core\Likeable;

/**
 * @ORM\Entity
 * @JMS\ExclusionPolicy("all")
 */
class Post extends Likeable
{
    /**
     * Au nom de quel club a été publié l'event, null si aucun club
     * @ORM\ManyToOne(targetEntity="KI\UpontBundle\Entity\Users\Club", cascade={"persist"})
     * @JMS\Expose
     * @Assert\Valid()
     */
    protected $authorClub;

    /**
     * Auteur réel
     * @ORM\ManyToOne(targetEntity="KI\UpontBundle\Entity\Users\User", cascade={"persist"})
     * @JMS\Expose
     * @Assert\Valid()
     */
    protected $authorUser;

    /**
     * Date (timestamp)
     * @ORM\Column(name="date", type="integer")
     * @JMS\Expose
     * @Assert\Type("integer")
     */
    protected $date;

    /**
     * Corps du texte
     * @ORM\Column(name="text", type="text")
     * @JMS\Expose
     * @Assert\Type("string")
     * @Assert\NotBlank()
     */
    protected $text;

    /**
     * Image personnalisée
     * @ORM\OneToOne(targetEntity="KI\UpontBundle\Entity\Image", cascade={"persist", "remove"})
     * @Assert\Valid()
     */
    protected $image;

    /**
     * Délivre l'url de l'image du post par défaut, l'url de l'image du club si l'image du post est null, l'image de l'auteur si les deux premières sont null, et null si les trois images sont null
     * @JMS\VirtualProperty()
     */
    public function imageUrl()
    {
        if ($this->image !== null) return $this->image->getWebPath();
        else if ($this->authorClub !== null && $this->authorClub->getImage() !== null) return $this->authorClub->getImage()->getWebPath();
    }







    //===== GENERATED AUTOMATICALLY =====//


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param integer $date
     * @return Newsitem
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return integer
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Post
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Newsitem
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set authorClub
     *
     * @param \KI\UpontBundle\Entity\Club $authorClub
     * @return Newsitem
     */
    public function setAuthorClub(\KI\UpontBundle\Entity\Users\Club $authorClub = null)
    {
        $this->authorClub = $authorClub;

        return $this;
    }

    /**
     * Get authorClub
     *
     * @return \KI\UpontBundle\Entity\Club
     */
    public function getAuthorClub()
    {
        return $this->authorClub;
    }

    /**
     * Set authorUser
     *
     * @param \KI\UpontBundle\Entity\User $authorUser
     * @return Newsitem
     */
    public function setAuthorUser(\KI\UpontBundle\Entity\Users\User $authorUser = null)
    {
        $this->authorUser = $authorUser;

        return $this;
    }

    /**
     * Get authorUser
     *
     * @return \KI\UpontBundle\Entity\User
     */
    public function getAuthorUser()
    {
        return $this->authorUser;
    }

    /**
     * Set image
     *
     * @param \KI\UpontBundle\Entity\Image $image
     * @return Newsitem
     */
    public function setImage(\KI\UpontBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \KI\UpontBundle\Entity\Image
     */
    public function getImage()
    {
        return $this->image;
    }
}
