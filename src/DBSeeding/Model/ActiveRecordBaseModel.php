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

    public function storeInDB()
    {
        self::$model_store[static::class][$this->getPrimaryId()] = $this;
    }

    protected function recordStored($dirtyData = null)
    {

    }

    abstract public function getPrimaryId();

    public static function findByPrimary(int $id): ?static
    {
        return self::$model_store[static::class][$id] ?? null;
    }
}
