<?php

namespace Kdubuc\Message\Command;

use Kdubuc\Message\Message;
use League\Tactician\Plugins\NamedCommand\NamedCommand;

abstract class Command extends Message implements NamedCommand
{
    /**
     * Returns the name of the command.
     */
    public function getCommandName()
    {
        return $this->getName();
    }
}
