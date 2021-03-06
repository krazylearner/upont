<?php

namespace KI\ClubinfoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use KI\CoreBundle\Entity\Likeable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @JMS\ExclusionPolicy("all")
 */
class Tuto extends Likeable
{
    /**
     * Corps du texte
     * @ORM\Column(name="text", type="text")
     * @JMS\Expose
     * @Assert\NotBlank()
     */
    protected $text;

    /**
     * Date (timestamp)
     * @ORM\Column(name="date", type="integer")
     * @JMS\Expose
     */
    protected $date;

    /**
     * Icône (utilisée par l'application mobile)
     * @ORM\Column(name="icon", type="string", nullable=true)
     * @JMS\Expose
     */
    protected $icon;

    public function __construct()
    {
        parent::__construct();
        $this->date = time();
    }

    /**
     * Set date
     *
     * @param integer $date
     * @return Tuto
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
     * @return Tuto
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
     * Set icon
     *
     * @param string $icon
     * @return Tuto
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }
}
