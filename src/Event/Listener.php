<?php

namespace API\Message\Event;

use League\Event\AbstractListener;
use League\Event\EventInterface as Event;

abstract class Listener extends AbstractListener
{
    /**
     * Handle the event.
     */
    public function handle(Event $event) : void
    {
        $method_name = 'handle'.(new ReflectionClass($event))->getShortName();

        if (method_exists($this, $method_name)) {
            $this->$method_name($event);
        }
    }
}
