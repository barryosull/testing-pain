<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\EventListener;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCode\StatusChangedEvent;

class VerificationCodeRepository
{
    public function __construct(
        private EventListener $event_listener
    ){}

    public function store(VerificationCode $verification_code)
    {
        $verification_code->store();
        $this->event_listener->broadcast(new StatusChangedEvent(
            $verification_code->account_id,
            $verification_code->verification_status
        ));
    }
}