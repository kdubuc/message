<?php

namespace Kdubuc\Message\Event;

interface Listener
{
    public function __invoke(Event $event) : void;
}
