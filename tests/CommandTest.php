<?php

use Kdubuc\Message\Message;
use Kdubuc\Message\Command\Bus;
use PHPUnit\Framework\TestCase;
use Kdubuc\Message\Command\Command;
use Kdubuc\Message\Command\Handler;

class CommandTest extends TestCase
{
    public function testCommandIsAMessage()
    {
        $command = new class() extends Command {
        };

        $this->assertInstanceOf(Message::class, $command);
        $this->assertInstanceOf(League\Tactician\Plugins\NamedCommand\NamedCommand::class, $command);

        $this->assertSame($command->getName(), $command->getCommandName());
    }

    public function testCommandBus()
    {
        $db             = new class() {
            private $db = [];

            public function all()
            {
                return $this->db;
            }

            public function push($value)
            {
                $this->db[] = $value;
            }
        };

        $handler = new class($db) extends Handler {
            public function __construct($db)
            {
                $this->db = $db;
            }

            public function __invoke(Command $command)
            {
                $this->db->push($command->get('message'));
            }
        };

        $event = $this->getMockBuilder(Event::class)->getMock();

        $command = new class('test message') extends Command {
            public function __construct(string $message)
            {
                $this->fillPayload([
                    'message' => $message,
                ]);
            }
        };

        $bus = new Bus();

        $this->assertInstanceOf(League\Tactician\CommandBus::class, $bus);

        $bus->subscribe($handler, get_class($command));

        $this->assertCount(0, $db->all());

        $bus->dispatchBatch([$command]);

        $this->assertCount(1, $db->all());

        $this->expectException(Exception::class);

        $bus->dispatch($command);
    }
}
