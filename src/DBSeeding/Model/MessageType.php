<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

class MessageType extends ActiveRecordBaseModel
{
    public function __construct(
        public int $message_type_id
    ){}

    public function getPrimaryId(): int
    {
        return $this->message_type_id;
    }
}
