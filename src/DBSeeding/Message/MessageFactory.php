<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Message;

class MessageFactory
{
    public function makeVerificationFailedMessage(int $account_id): Message
    {
        if ($account_id % 2 === 0) {
            return new VerificationFailedClosed();
        }
        return new VerificationFailedWarning();
    }
}
