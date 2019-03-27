<?php

use Kdubuc\Message\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testMessagePayload()
    {
        $message = new class('Test message', 5) extends Message {
            public function __construct(string $data, int $number, string $default = 'test')
            {
                $this->fillPayload(get_defined_vars());
            }
        };

        $this->assertEquals('Test message', $message->getPayload()['data']);
        $this->assertEquals(5, $message->getPayload()['number']);
        $this->assertEquals('test', $message->getPayload()['default']);
        $this->assertEquals([
            'data'    => 'string',
            'number'  => 'int',
            'default' => 'string',
        ], $message->getAttributes());
    }

    public function testMessagePayloadMustNotContainsObjects()
    {
        $this->expectException(Exception::class);

        $message = new class(new stdClass()) extends Message {
            public function __construct(object $forbidden_object)
            {
                $this->fillPayload(get_defined_vars());
            }
        };
    }

    public function testRecordFromArray()
    {
        $message = new class('Test message') extends Message {
            public function __construct(string $data)
            {
                $this->fillPayload(get_defined_vars());
            }
        };

        $message_from_array = $message::recordFromArray($message->toArray() + ['attribute_to_be_removed' => 'test']);

        $this->assertEquals($message, $message_from_array);
    }

    public function testId()
    {
        $message = $this->getMockBuilder(Message::class)->getMock();

        $this->assertIsString($id = $message->getId());
        $this->assertEquals($id, $message->getId());
    }

    public function testRecordDate()
    {
        $message = $this->getMockBuilder(Message::class)->getMock();

        $this->assertInstanceOf(Datetime::class, $date = $message->getRecordDate());
        $this->assertEquals($date, $message->getRecordDate());
    }

    public function testJsonSerializable()
    {
        $message = new class('Test message', 5) extends Message {
            public function __construct(string $data, int $number, string $default = 'test')
            {
                $this->fillPayload(get_defined_vars());
            }
        };

        $this->assertInstanceOf(JsonSerializable::class, $message);
        $this->assertEquals([
            'id'          => $message->getId(),
            'name'        => $message->getName(),
            'class_name'  => get_class($message),
            'record_date' => $message->getRecordDate()->format('Y-m-d\TH:i:s.u'),
            'payload'     => [
                'data'    => 'Test message',
                'number'  => 5,
                'default' => 'test',
            ],
        ], $message->jsonSerialize());
        $this->assertSame($message->jsonSerialize(), $message->toArray());
    }
}
