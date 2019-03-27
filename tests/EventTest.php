<?php

use Kdubuc\Message\Message;
use Kdubuc\Message\Event\Event;
use PHPUnit\Framework\TestCase;
use Kdubuc\Message\Event\Stream;
use Kdubuc\Message\Event\Listener;

class EventTest extends TestCase
{
    public function testEventIsAMessage()
    {
        $event = $this->getMockBuilder(Event::class)->getMock();

        $this->assertInstanceOf(Message::class, $event);
        $this->assertInstanceOf(League\Event\EventInterface::class, $event);
    }

    public function testEventCanBeStopped()
    {
        $event = new class() extends Event {
        };

        $event->stopPropagation();

        $this->assertTrue($event->isPropagationStopped());
    }

    public function testEmitterAware()
    {
        $event = new class() extends Event {
        };

        $event->setEmitter($emitter = $this->createMock(League\Event\EmitterInterface::class));

        $this->assertEquals($emitter, $event->getEmitter());
    }

    public function testListener()
    {
        $event = new class() extends Event {
            public function getName() : string
            {
                return 'EventTest';
            }
        };

        $listener = $this->getMockBuilder(Listener::class)->setMethods(['handleEventTest'])->getMock();
        $listener->expects($this->once())->method('handleEventTest')->with($this->equalTo($event));

        $listener->handle($event);

        $this->assertInstanceOf(League\Event\AbstractListener::class, $listener);
    }

    public function testEventStream()
    {
        $event1 = new class() extends Event {
            public function getName() : string
            {
                return 'EventTest1';
            }
        };

        $event2 = new class() extends Event {
            public function getName() : string
            {
                return 'EventTest2';
            }
        };

        $stream = new Stream();

        $this->assertInstanceOf(League\Event\Emitter::class, $stream);
        $this->assertInstanceOf(IteratorAggregate::class, $stream);
        $this->assertCount(0, $stream->getEventsEmitted());

        $listener1 = $this->createMock(Listener::class);
        $listener1->expects($this->exactly(2))->method('handle')->withConsecutive([$this->equalTo($event1)], [$this->equalTo($event2)]);
        $stream->attachListener($listener1);

        $listener2 = $this->createMock(Listener::class);
        $listener2->expects($this->once())->method('handle')->with($this->equalTo($event2));
        $stream->attachListener($listener2, ['EventTest2']);

        $stream->emit($event1);
        $stream->emit($event2);

        $this->assertCount(2, $stream->getEventsEmitted());
        $this->assertSame($event1, $stream->find('EventTest1'));
        $this->assertSame($event1->getEmitter(), $stream);
    }
}
