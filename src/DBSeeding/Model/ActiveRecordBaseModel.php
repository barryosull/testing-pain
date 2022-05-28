<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

abstract class ActiveRecordBaseModel
{
    protected static $model_store = [];

    public function store()
    {
        $this->storeInDB();
        $this->recordStored();
    }

    public static function clearStore(): void
    {
        self::$model_store = [];
    }

    public function storeInDB(): void
    {
        self::$model_store[static::class][$this->getPrimaryId()] = $this;
    }

    protected function recordStored($dirtyData = null): void
    {

    }

    abstract public function getPrimaryId(): int;

    public static function findByPrimary(int $id): ?static
    {
        return self::$model_store[static::class][$id] ?? null;
    }

    protected static $incrementing_id = 0;

    protected function autoincrementId(): int
    {
        self::$incrementing_id++;
        return self::$incrementing_id;
    }

    public static function makeFromDbRow(array $db_row): static {
        return new static(...$db_row);
    }
}
