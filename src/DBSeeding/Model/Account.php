<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

class Account
{
    /** @var int */
    public $id;

    public static function find(int $account_id): ?Account
    {
        // details omitted ...
        return null;
    }
}
