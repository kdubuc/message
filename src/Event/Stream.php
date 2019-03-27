<?php

namespace Kdubuc\Message\Event;

use Exception;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use League\Event\Emitter;

class Stream extends Emitter implements IteratorAggregate
{
    /**
     * Build the stream.
     */
    public function __construct()
    {
        $this->events_emitted = [];
    }

    /**
     * Get all events emitted in chronological order (old -> new).
     */
    public function getEventsEmitted() : array
    {
        return $this->events_emitted;
    }

    /**
     * Emit an event.
     */
    public function emit($event) : Event
    {
        if ($this->hasListeners($event->getName()) || $this->hasListeners('*')) {
            $this->events_emitted[] = $event;
        }

        return parent::emit($event);
    }

    /**
     * Fin an event (based on event's name).
     */
    public function find(string $event_name) : Event
    {
        // Get all messages dispatched with the same name
        $messages = array_filter($this->getEventsEmitted(), function ($message) use ($event_name) {
            return $message->getName() === $event_name;
        });

        // Get the last element (most recent) in the result
        $message = array_pop($messages);

        // If the element is not null, return the event !
        if (null !== $message) {
            return $message;
        }

        throw new Exception('Event not found');
    }

    /**
     * Enable Traversable.
     */
    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->getEventsEmitted());
    }

    /**
     * Affect a listener to handle multiple events.
     */
    public function attachListener(Listener $listener, array $events_names = []) : void
    {
        // If no events names are given, listen ALL events.
        if (0 == \count($events_names)) {
            $events_names[] = '*';
        }

        // Attach listeners for all events given
        foreach ($events_names as $event_name) {
            $this->addListener($event_name, $listener);
        }
    }
}
