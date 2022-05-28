<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

class Account extends ActiveRecordBaseModel
{
    public function __construct(
        public int $account_id
    ){}

    public static function find(int $account_id): ?Account
    {
        return self::findByPrimary($account_id);
    }

    public function getPrimaryId(): int
    {
        return $this->account_id;
    }
}
