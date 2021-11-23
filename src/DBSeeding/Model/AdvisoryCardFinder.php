<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

class AdvisoryCardFinder
{
    /**
     * @return AdvisoryCard[]
     */
    public function findDisplayableForAccount(Account $account): array {
        return [];
    }
}
