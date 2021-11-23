<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\AdvisoryCard;

use Barryosull\TestingPain\DBSeeding\Model\Account;

interface Card
{
    public function createForAccount(Account $account);

    public function markAsAddressed(Account $account);
}
