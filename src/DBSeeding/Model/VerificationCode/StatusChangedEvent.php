<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model\VerificationCode;

class StatusChangedEvent
{
    public function __construct(
        public int $account_id,
        public string $status
    ){}
}
