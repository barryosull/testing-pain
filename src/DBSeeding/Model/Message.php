<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

class Message extends ActiveRecordBaseModel
{
    CONST VERIFICATION_FAILED_TYPE_ID = 1;

    /** @var int */
    protected $message_type_id;

    public function __construct(int $account_id, int $message_type_id)
    {

    }

    public function display(): void
    {

    }

    public function clear(): void
    {

    }

    /**
     * @param int $account_id
     * @param int $type_id
     * @return Message[]
     */
    public static function findActive(int $account_id, int $type_id): array {
        return [];
    }

    /**
     * @param int $account_id
     * @param int $type_id
     * @return Message[]
     */
    public static function findByType(int $account_id, int $type_id): array {
        return [];
    }
}
