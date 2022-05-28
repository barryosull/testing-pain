<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\Model\Message;
use Barryosull\TestingPain\DBSeeding\Model\MessageType;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCode;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCodeStatus;
use Barryosull\TestingPainTests\DBTestCase;

class VerificationCodeTest extends DBTestCase
{
    CONST ACCOUNT_ID = 1;
    CONST CODE = '1111';
    CONST CODE_2 = '2222';
    CONST VERIFICATION_LAST_CHECKED_AT = 1919191919;
    CONST UPDATE_DATE = 1609000000;

    public function test_messages_handle_on_store()
    {
        $message_type_id = Message::VERIFICATION_FAILED_TYPE_ID;

        $verification_code = $this->makeVerificatonCode();
        $message_type = $this->makeMessageType($message_type_id);

        $this->givenVerificationCode($verification_code);
        $this->givenMessageType($message_type);

        $this->whenVerificationCodeStatusChanges(VerificationCodeStatus::FAILED);
        $this->verifyMessageIsDisplayed($message_type_id);

        $this->whenVerificationCodeStatusChanges(VerificationCodeStatus::VERIFIED);
        $this->verifyMessageIsCleared($message_type_id);
        
        $this->whenVerificationCodeStatusChanges(VerificationCodeStatus::FAILED);
        $this->verifyMessageIsDisplayed($message_type_id);
    }

    private function makeVerificatonCode(): VerificationCode {
        return VerificationCode::makeFromDbRow([
            'verification_code_id' => 1,
            'account_id' => self::ACCOUNT_ID,
            'code' => self::CODE,
            'verification_status' => VerificationCodeStatus::UNCHECKED,
            'verification_last_checked_at' => self::VERIFICATION_LAST_CHECKED_AT,
        ]);
    }

    private function givenVerificationCode(VerificationCode $verification_code): void
    {
        $verification_code->store();
    }

    private function makeMessageType(int $message_type_id): MessageType
    {
        return MessageType::makeFromDbRow([
            'message_type_id' => $message_type_id,
            'next_message_id' => null,
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
        ]);
    }

    private function givenMessageType(MessageType $message_type)
    {
        $message_type->store();
    }

    private function whenVerificationCodeStatusChanges(string $status)
    {
        $verification_code = VerificationCode::find(self::ACCOUNT_ID);
        $verification_code->verification_status = $status;
        $verification_code->store();
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
