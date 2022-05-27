<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

class Account extends ActiveRecordBaseModel
{
    /** @var int */
    public $account_id;

    public static function find(int $account_id): ?Account
    {
        return self::findByPrimary($account_id);
    }

    public function getPrimaryId()
    {
        return $this->account_id;
    }
}
