<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\EventListener;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCode;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCode\StatusChangedEvent;
use Barryosull\TestingPainTests\DBTestCase;

class VerificationCodeTest extends DBTestCase
{
    CONST ACCOUNT_ID = 1;
    CONST CODE = '1111';

    protected function getDBSeedData(): array
    {
        return [];
    }

    public function test_verification_status_message_is_sent_on_store()
    {
        $verification_code = $this->makeVerificationCode();

        $message_sent = false;
        EventListener::listenTo(StatusChangedEvent::class, function(StatusChangedEvent $message) use (&$message_sent) {
            $message_sent = true;
        });

        $verification_code->store();

        $this->assertTrue($message_sent, "Expected message of type {StatusChangedMessage:class} to sent");
    }

    private function makeVerificationCode(): VerificationCode
    {
        $code = new VerificationCode();
        $code->verification_code_id = 1;
        $code->account_id = self::ACCOUNT_ID;
        $code->code = self::CODE;
        return $code;
    }
}
