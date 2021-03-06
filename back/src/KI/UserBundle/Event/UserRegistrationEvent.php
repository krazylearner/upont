<?php

namespace KI\UserBundle\Event;

use FOS\UserBundle\Model\UserInterface;
use KI\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class UserRegistrationEvent extends Event
{
    protected $user;
    protected $attributes;

    public function __construct(UserInterface $user, array $attributes)
    {
        $this->user = $user;
        $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
