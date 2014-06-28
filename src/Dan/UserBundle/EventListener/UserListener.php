<?php

namespace Dan\UserBundle\EventListener;

use Symfony\Component\EventDispatcher\Event;
use FOS\UserBundle\Model\UserInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ObjectManager;

class UserListener
{
    public function prePersist(LifecycleEventArgs $event)
    {
        if ($event->getEntity() instanceof UserInterface) {
            $user = $event->getEntity();
            if (!$user->getDisplayname()) {
                $user->generateDisplayname();
            }
        }
    }
}
