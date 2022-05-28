<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\DBSeeding\Application;

use Barryosull\TestingPain\DBSeeding\EventListener;
use Barryosull\TestingPain\DBSeeding\Application\MessageService;
use Barryosull\TestingPain\DBSeeding\Model\Message;
use Barryosull\TestingPain\DBSeeding\Model\MessageType;
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

    public function test_verification_failed_message_is_updated_when_verification_code_status_changes()
    {
        $message_type = MessageTypeFactory::makeWithTypeId(Message::VERIFICATION_FAILED_TYPE_ID);
        $this->givenMessageTypeExists($message_type);

        $failed_event = $this->makeStatusChangedEvent(VerificationCodeStatus::FAILED);
        $verified_event = $this->makeStatusChangedEvent(VerificationCodeStatus::VERIFIED);

        $this->event_listener->broadcast($failed_event);
        $this->verifyMessageIsDisplayed($message_type->message_type_id);

        $this->event_listener->broadcast($verified_event);
        $this->verifyMessageIsCleared($message_type->message_type_id);

        $this->event_listener->broadcast($failed_event);
        $this->verifyMessageIsDisplayed($message_type->message_type_id);
    }

    private function makeStatusChangedEvent(string $status): StatusChangedEvent
    {
        return new StatusChangedEvent(
            self::ACCOUNT_ID,
            $status
        );
    }

    private function givenMessageTypeExists(MessageType $message_type)
    {
        $message_type->store();
    }

    /**
     * @param int $message_type_id
     */
    protected function verifyMessageIsDisplayed(int $message_type_id): void
    {
        $this->assertEquals(1, count(Message::findActive(self::ACCOUNT_ID, $message_type_id)));
    }

    /**
     * @param int $message_type_id
     */
    protected function verifyMessageIsCleared(int $message_type_id): void
    {
        $this->assertEquals(0, count(Message::findActive(self::ACCOUNT_ID, $message_type_id)));
    }
}