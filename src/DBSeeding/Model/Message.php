<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

abstract class Message extends ActiveRecordBaseModel
{
    /** @var int */
    protected $message_id;


    /** @var int */
    public $occurrence = 0;

    /**
     * @return Message[]
     */
    public static function findActive(Account $account): array {
        return [];
    }
}
