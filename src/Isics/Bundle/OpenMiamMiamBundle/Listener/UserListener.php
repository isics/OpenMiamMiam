<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Listener;

use Isics\Bundle\OpenMiamMiamBundle\Formatter\UserFormatter;
use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use JMS\Serializer\EventDispatcher\ObjectEvent;

class UserListener
{
    /**
     * @var UserFormatter
     */
    protected $userFormatter;

    /**
     * Constructor
     *
     * @param UserFormatter $userFormatter User formatter
     */
    public function __construct(UserFormatter $userFormatter)
    {
        $this->userFormatter = $userFormatter;
    }

    /**
     * Json représentation of a user
     *
     * @param User $user the user
     *
     * @return string
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $user = $event->getObject();

        if ($user instanceof User) {
            $event->getVisitor()->addData('identity', $this->userFormatter->formatUserIdentity($user));
        }
    }
}