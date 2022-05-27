<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

class MessageType extends ActiveRecordBaseModel
{
    /** @var int */
    public $message_type_id;

    public function getPrimaryId()
    {
        return $this->message_type_id;
    }
}
