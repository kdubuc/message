<?php

namespace Kdubuc\Message;

use Datetime;
use ArrayIterator;
use ReflectionClass;
use JsonSerializable;
use ReflectionMethod;

abstract class Message implements JsonSerializable
{
    /**
     * Record new message.
     */
    public static function recordFromArray(array $data) : self
    {
        $id          = $data['id'];
        $recorded_on = Datetime::createFromFormat('Y-m-d\TH:i:s.u', $data['record_date']);
        $payload     = $data['payload'];
        $class_name  = $data['class_name'];

        $attributes = $class_name::getAttributes();

        $construct_parameters = [];

        foreach ($attributes as $attribute => $type) {
            if (\array_key_exists($attribute, $payload)) {
                $value                  = $payload[$attribute];
                $construct_parameters[] = $value;
            }
        }

        return $class_name::record($id, $recorded_on, $construct_parameters);
    }

    /**
     * Record new message.
     */
    public static function record(string $id, DateTime $recorded_on, ...$construct_parameters) : self
    {
        $message = new static(...$construct_parameters[0]);

        $message->id          = $id;
        $message->record_date = $recorded_on;

        return $message;
    }

    /**
     * Record a new message with a new id and the date of the day.
     */
    public static function recordNow(...$construct_parameters) : self
    {
        return static::record(uniqid(), new Datetime(), $construct_parameters);
    }

    /**
     * Get the short message name (e.g. the type condensed).
     */
    public function getName() : string
    {
        $reflection = new ReflectionClass($this);

        return $reflection->getShortName();
    }

    /**
     * Get message id.
     */
    public function getId() : string
    {
        if (empty($this->id)) {
            $this->id = uniqid();
        }

        return $this->id;
    }

    /**
     * Payload array will be filtered to inlcude only construct params.
     */
    public function getPayload() : array
    {
        $attributes = self::getAttributes();

        $payload = [];

        foreach ($attributes as $name => $type) {
            $payload[$name] = $this->get($name);
        }

        return $payload;
    }

    /**
     * Get the recorded date.
     */
    public function getRecordDate() : Datetime
    {
        if (empty($this->record_date)) {
            $this->record_date = new DateTime();
        }

        return $this->record_date;
    }

    /**
     * Get message attributes names / types. Attributes are __construct signature.
     */
    public static function getAttributes() : array
    {
        $method = new ReflectionMethod(static::class, '__construct');

        $attributes = array_reduce($method->getParameters(), function ($result, $parameter) {
            $result[$parameter->getName()] = $parameter->getType()->__toString();

            return $result;
        }, []);

        return $attributes;
    }

    /**
     * Fill the message payload according to the __contruct signature and data values.
     */
    public function fillPayload(array $data = []) : self
    {
        $attributes = self::getAttributes();

        $data = (array) new ArrayIterator(array_filter($data, function ($k) use ($attributes) {
            return \in_array($k, array_keys($attributes));
        }, \ARRAY_FILTER_USE_KEY));

        foreach ($attributes as $name => $type) {
            $value = $data[$name];

            $this->set($name, $value);
        }

        return $this;
    }

    /**
     * Get a payload's property.
     */
    public function get(string $name)
    {
        return $this->{"payload_$name"};
    }

    /**
     * Set a payload's property.
     */
    public function set(string $name, $value) : self
    {
        if (\is_object($value)) {
            throw new \Exception("Le payload d'un message ne peut contenir un objet");
        }

        $attributes = $this->getAttributes();

        if (!\array_key_exists($name, $attributes)) {
            throw new \Exception("La propriété $name n'existe pas dans la signature du message");
        }

        if (!\gettype($value) === $attributes[$name]) {
            throw new \Exception("La propriété $name doit être du type ".$attributes[$name]);
        }

        $this->{"payload_$name"} = $value;

        return $this;
    }

    /**
     * Return array representation.
     */
    public function toArray() : array
    {
        return [
            'id'          => $this->getId(),
            'name'        => $this->getName(),
            'class_name'  => static::class,
            'record_date' => $this->getRecordDate()->format('Y-m-d\TH:i:s.u'),
            'payload'     => $this->getPayload(),
        ];
    }

    /**
     * Return JSON representation.
     */
    public function jsonSerialize() : array
    {
        return $this->toArray();
    }
}
