<?php

namespace Kdubuc\Message\Command;

use Exception;
use ReflectionParameter;
use League\Tactician\CommandBus as TacticianBus;
use League\Tactician\Handler\Locator\InMemoryLocator;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Exception\MissingHandlerException;
use League\Tactician\Handler\MethodNameInflector\InvokeInflector;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;

class Bus extends TacticianBus
{
    /**
     * Build the bus.
     */
    public function __construct(array $middlewares = [])
    {
        $this->init                = false;
        $this->middlewares         = $middlewares;
        $this->locator             = new InMemoryLocator();
        $this->extractor           = new ClassNameExtractor();
        $this->inflector           = new InvokeInflector();
        $this->dispatched_commands = [];
    }

    /**
     * Dispatch a command to its appropriate handler.
     */
    public function dispatch(Command $command) : void
    {
        if (!$this->init) {
            $this->init = true;
            parent::__construct(array_merge($this->middlewares, [
                new CommandHandlerMiddleware($this->extractor, $this->locator, $this->inflector),
            ]));
        }

        // We don't dispatch a message that was already dispatched before
        if (\array_key_exists($command->getId(), $this->dispatched_commands)) {
            throw new Exception($command->getName().' (id: '.$command->getId().') already dispatched');
        }

        // Register the dispatched message
        $this->dispatched_commands[$command->getId()] = $command;

        try {
            $this->handle($command);
        } catch (MissingHandlerException $e) {
            // Avoid missing handler exception. If there isn't handler for the message,
            // the show must go on !
            return;
        }
    }

    /**
     * Dispatch a batch of commands.
     */
    public function dispatchBatch(array $commands) : void
    {
        foreach ($commands as $command) {
            $this->dispatch($command);
        }
    }

    /**
     * Map a command handler with a specific command message.
     */
    public function subscribe(Handler $handler, string $command_class_name = null) : void
    {
        // If no command class name provided, we guess it from the invoke arg handler method
        if (null === $command_class_name) {
            $handler_parameter_reflection = new ReflectionParameter([\get_class($handler), '__invoke'], 0);

            $command_class_name = $handler_parameter_reflection->getClass()->name;
        }

        $this->locator->addHandler($handler, $command_class_name);
    }
}
