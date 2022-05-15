<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Message;

use Barryosull\TestingPain\DBSeeding\Model\Account;

interface Message
{
    public function create(Account $account);

    public function clear(Account $account);
}
