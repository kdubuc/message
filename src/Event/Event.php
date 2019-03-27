<?php

namespace Kdubuc\Message\Event;

use Kdubuc\Message\Message;
use League\Event\EventInterface;
use League\Event\EmitterInterface as Emitter;

abstract class Event extends Message implements EventInterface
{
    /**
     * Set the Emitter.
     */
    public function setEmitter(Emitter $emitter) : self
    {
        $this->emitter = $emitter;

        return $this;
    }

    /**
     * Get the Emitter.
     */
    public function getEmitter() : Emitter
    {
        return $this->emitter;
    }

    /**
     * Stop event propagation.
     */
    public function stopPropagation() : void
    {
        $this->propagation_stopped = true;
    }

    /**
     * Check whether propagation was stopped.
     */
    public function isPropagationStopped() : bool
    {
        return property_exists($this, 'propagation_stopped') && true === $this->propagation_stopped;
    }
}
