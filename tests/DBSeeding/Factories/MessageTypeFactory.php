<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\DBSeeding\Factories;

use Barryosull\TestingPain\DBSeeding\Model\MessageType;

class MessageTypeFactory
{
    public static function makeWithTypeId(int $type_id): MessageType
    {
        return new MessageType($type_id);
    }
}