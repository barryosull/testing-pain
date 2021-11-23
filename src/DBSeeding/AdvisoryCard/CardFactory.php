<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\AdvisoryCard;

class CardFactory
{
    public function makeVerificationFailedCard(int $account_id): Card
    {
        if ($account_id % 2 === 0) {
            return new VerificationFailedClosed();
        }
        return new VerificationFailedWarning();
    }
}
