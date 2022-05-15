<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Message;

use Barryosull\TestingPain\DBSeeding\Model\Account;
use Barryosull\TestingPain\DBSeeding\Model\Message;

class VerificationFailed extends Message
{
    protected $message_id = 1;

    public function create(Account $account)
    {

    }

    public function clear(Account $account)
    {

    }
}
