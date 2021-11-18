<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\AdvisoryCard;

class CardFactory
{
    public function makeVerificationFailedCard(int $shop_id): Card {
        if ($shop_id % 2 === 0) {
            return new IDVerificationFailedClosed();
        }
        return new IDVerificationFailedWarning();
    }
}
