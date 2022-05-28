<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\EventListener;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCode;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCode\StatusChangedEvent;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCodeRepository;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCodeStatus;
use PHPUnit\Framework\TestCase;

class VerificationCodeRepositoryTest extends TestCase
{
    CONST ACCOUNT_ID = 1;
    CONST CODE = '1111';

    private EventListener $event_listener;

    private VerificationCodeRepository $verification_code_repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event_listener = $this->createMock(EventListener::class);

        $this->verification_code_repo = new VerificationCodeRepository($this->event_listener);
    }

    public function test_verification_status_message_is_sent_on_store()
    {
        $verification_code = $this->makeVerificationCode();

        $this->expectEventToBeBroadcast(new StatusChangedEvent(
            $verification_code->account_id,
            $verification_code->verification_status
        ));

        $this->verification_code_repo->store($verification_code);
    }

    private function makeVerificationCode(): VerificationCode
    {
        return new VerificationCode(
            1,
            self::ACCOUNT_ID,
            self::CODE,
            VerificationCodeStatus::UNCHECKED
        );
    }

    private function expectEventToBeBroadcast(StatusChangedEvent $event)
    {
        $this->event_listener->expects($this->once())
            ->method('broadcast')
            ->with($event);
    }
}
