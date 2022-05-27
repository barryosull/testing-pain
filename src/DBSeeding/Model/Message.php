<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

class Message extends ActiveRecordBaseModel
{
    CONST VERIFICATION_FAILED_TYPE_ID = 1;

    /** @var int */
    public $message_id;

    /** @var int */
    public $account_id;

    /** @var int */
    public $message_type_id;

    /** @var bool  */
    public $is_active;

    public function __construct(int $account_id, int $message_type_id)
    {
        $this->account_id = $account_id;
        $this->message_type_id = $message_type_id;
        $this->is_active = true;
    }

    public function display(): void
    {
        $this->is_active = true;
    }

    public function clear(): void
    {
        $this->is_active = false;
    }

    /**
     * @param int $account_id
     * @param int $type_id
     * @return self[]
     */
    public static function findActive(int $account_id, int $type_id): array
    {
        return array_filter(self::$model_store[static::class] ?? [], function(self $record) use ($account_id, $type_id) {
            return $record->account_id === $account_id && $record->message_type_id === $type_id && $record->is_active;
        });
    }

    /**
     * @param int $account_id
     * @param int $type_id
     * @return self|null
     */
    public static function findByType(int $account_id, int $type_id): ?self
    {
        $rows = array_filter(self::$model_store[static::class] ?? [], function(self $record) use ($account_id, $type_id) {
            return $record->account_id === $account_id && $record->message_type_id === $type_id;
        });

        return $rows[0] ?? null;
    }

    public function getPrimaryId()
    {
        return $this->message_id;
    }
}
