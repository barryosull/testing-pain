<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Message;

use Barryosull\TestingPain\DBSeeding\Model\Account;

class VerificationFailedWarning implements Message
{
    public function create(Account $account)
    {

    }

    public function markAsAddressed(Account $account)
    {

    }
}
