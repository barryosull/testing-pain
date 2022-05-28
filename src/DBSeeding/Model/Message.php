<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

class Message extends ActiveRecordBaseModel
{
    CONST VERIFICATION_FAILED_TYPE_ID = 1;

    public function __construct(
        public int $account_id,
        public int $message_type_id,
        public ?int $message_id = null,
        public bool $is_active = true
)
    {
        $this->message_id = $message_id ?: $this->autoincrementId();
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
        return array_filter(self::$model_store[self::class] ?? [], function(self $record) use ($account_id, $type_id) {
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
        $rows = array_filter(self::$model_store[self::class] ?? [], function(self $record) use ($account_id, $type_id) {
            return $record->account_id === $account_id && $record->message_type_id === $type_id;
        });

        return array_pop($rows) ?? null;
    }

    public function getPrimaryId(): int
    {
        return $this->message_id;
    }
}
