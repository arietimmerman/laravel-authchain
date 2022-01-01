<?php

namespace ArieTimmerman\Laravel\AuthChain\Module;

class Message implements \JsonSerializable
{
    public $message;
    public $type;

    const TYPE_INFO = 'info';
    const TYPE_ERROR = 'error';

    private function __construct($message, $type)
    {
        $this->message = $message;
        $this->type = $type;
    }

    public static function info($message)
    {
        return new static($message, static::TYPE_INFO);
    }

    public static function error($message)
    {
        return new static($message, static::TYPE_ERROR);
    }

    public static function arrayFromJson($json)
    {
        if (empty($json)) {
            return [];
        }

        $result = [];

        foreach ($json as $j) {
            $result[] = new Message($j->message, $j->type);
        }

        return $result;
    }

    public function jsonSerialize()
    {
        return [
            'type' => $this->type,
            'message' => $this->message
        ];
    }
}
