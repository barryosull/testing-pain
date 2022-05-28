<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\DBSeeding\Application;

use Barryosull\TestingPain\DBSeeding\EventListener;
use Barryosull\TestingPain\DBSeeding\Application\MessageService;
use Barryosull\TestingPain\DBSeeding\Model\Message;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCode\StatusChangedEvent;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCodeStatus;
use Barryosull\TestingPainTests\DBSeeding\Factories\MessageTypeFactory;
use PHPUnit\Framework\TestCase;

class MessageServiceTest extends TestCase
{
    const ACCOUNT_ID = 1;

    private EventListener $event_listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event_listener = new EventListener();

        $message_service = new MessageService();
        $message_service->registerListeners($this->event_listener);
    }

    public function test_message_are_updated_when_verification_code_status_changes()
    {
        $message_type_id = Message::VERIFICATION_FAILED_TYPE_ID;

        $this->givenMessageType($message_type_id);

        $failed_event = $this->makeStatusChangedEvent(VerificationCodeStatus::FAILED);
        $this->event_listener->broadcast($failed_event);
        // We just saved a failed verification, so should have 1 message
        $this->assertEquals(1, count(Message::findActive(self::ACCOUNT_ID, $message_type_id)));


        $this->event_listener->broadcast($failed_event);
        // Current message hasn't been cleared (it's still active) so we should still have 1 active message
        $this->assertEquals(1, count(Message::findActive(self::ACCOUNT_ID, $message_type_id)));


        $verified_event = $this->makeStatusChangedEvent(VerificationCodeStatus::VERIFIED);
        $this->event_listener->broadcast($verified_event);
        // Saving a verified ID should clear the message, resulting in zero active
        $this->assertEquals(0, count(Message::findActive(self::ACCOUNT_ID, $message_type_id)));


        $this->event_listener->broadcast($failed_event);
        // Account saved another failed code. Should have 1 active message
        $this->assertEquals(1, count(Message::findActive(self::ACCOUNT_ID, $message_type_id)));
    }

    private function makeStatusChangedEvent(string $status): StatusChangedEvent
    {
        return new StatusChangedEvent(
            self::ACCOUNT_ID,
            $status
        );
    }

    private function givenMessageType(int $message_type_id)
    {
        $message_type = MessageTypeFactory::makeWithTypeId($message_type_id);
        $message_type->store();
    }
}