<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Message;

use Barryosull\TestingPain\DBSeeding\Model\Account;
use Barryosull\TestingPain\DBSeeding\Model\Message;

class VerificationFailed
{
    public const MESSAGE_TYPE_ID = 1;

    public function establish(Account $account): Message {
        $message = Message::findByType($account->id, self::MESSAGE_TYPE_ID);
        if ($message !== null) {
            return $message;
        }
        return new Message($account->id, self::MESSAGE_TYPE_ID);
    }
}
