<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\DBSeeding;

use Barryosull\TestingPain\DBSeeding\EventListener;
use Barryosull\TestingPain\DBSeeding\MessageService;
use Barryosull\TestingPain\DBSeeding\Model\Message;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCode\StatusChangedEvent;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCodeStatus;
use Barryosull\TestingPainTests\DBTestCase;

class MessageServiceTest extends DBTestCase
{
    const ACCOUNT_ID = 1;

    protected function getDBSeedData(): array
    {
        return [
            'message_types' => [
                [
                    'message_type_id' =>  Message::VERIFICATION_FAILED_TYPE_ID,
                    'next_message_id' =>  null,
                    'next_message_delay' =>  0,
                    'segment_data_url' =>  '',
                    'segment_description' =>  '',
                    'title' =>  'You are not verified',
                    'description' =>  'We need to verify you pronto buster',
                    'action_url' =>  'http//www.tiredorwired.com/',
                    'action_text' =>  'Do the thing',
                    'category' =>  'finances',
                    'priority' =>  4,
                    'status' =>  'published',
                    'start_date' =>  0,
                    'end_date' =>  0,
                    'create_date' =>  1387384309,
                    'update_date' =>  1387384309,
                    'duration' =>  -1,
                    'type' =>  256,
                    'dismissal_duration' =>  0,
                    'duration_type' =>  1,
                    'image_url' =>  '/images/account-tools/dashboard/notifications.svg',
                    'image_url_2' =>  '/images/account-tools/dashboard/notifications.svg',
                    'zone' => 22,
                    'target_platform' => 1,
                ]
            ],
        ];
    }

    public function test_message_are_updated_when_verification_code_status_changes()
    {
        $message_service = new MessageService();
        $message_service->bootEventListeners();

        $message_type_id = Message::VERIFICATION_FAILED_TYPE_ID;

        $failed_event = $this->makeStatusChangedEvent(VerificationCodeStatus::FAILED);

        EventListener::handle($failed_event);

        // We just saved a failed verification, so should have 1 message
        $this->assertEquals(1, count(Message::findActive(self::ACCOUNT_ID, $message_type_id)));

        // Current message hasn't been cleared (it's still active) so we should still have 1 active message
        $this->assertEquals(1, count(Message::findActive(self::ACCOUNT_ID, $message_type_id)));

        $verified_event = $this->makeStatusChangedEvent(VerificationCodeStatus::VERIFIED);

        EventListener::handle($verified_event);

        // Saving a verified ID should clear the message, resulting in zero active
        $this->assertEquals(0, count(Message::findActive(self::ACCOUNT_ID, $message_type_id)));

        EventListener::handle($failed_event);

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
}