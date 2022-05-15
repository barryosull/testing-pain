<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

class Message
{
    /**
     * @return Message[]
     */
    public static function findDisplayableForAccount(Account $account): array {
        return [];
    }
}
