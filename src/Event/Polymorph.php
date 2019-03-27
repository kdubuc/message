<?php

namespace Kdubuc\Message\Event;

abstract class Polymorph implements Listener
{
    /**
     * Handle the event.
     */
    public function __invoke(Event $event) : void
    {
        $method_name = 'handle'.$event->getName();

        if (method_exists($this, $method_name)) {
            $this->$method_name($event);
        }
    }
}
