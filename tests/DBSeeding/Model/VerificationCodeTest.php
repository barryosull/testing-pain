<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\Model\Account;
use Barryosull\TestingPain\DBSeeding\Model\Message;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCode;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCodeStatus;
use Barryosull\TestingPainTests\DBTestCase;

class VerificationCodeTest extends DBTestCase
{
    CONST ACCOUNT_ID = 1;
    CONST CODE = 1111;
    CONST CODE_2 = 2222;
    CONST VERIFICATION_LAST_CHECKED_AT = 1919191919;
    CONST UPDATE_DATE = 1609000000;

    protected function getDBSeedData(): array
    {
        return [
            'accounts' => [
                [
                    'account_id' => self::ACCOUNT_ID,
                    'user_id' => 1,
                    'name' => 'account 1',
                    'status' => 'active',
                ],
            ],
            'verification_codes' => [
                [
                    'verification_code_id' => 1,
                    'account_id' => self::ACCOUNT_ID,
                    'code' => self::CODE,
                    'verification_status' => VerificationCodeStatus::VERIFIED,
                    'verification_last_checked_at' => self::VERIFICATION_LAST_CHECKED_AT,
                    'update_date' => self::UPDATE_DATE,
                ],
                [
                    'verification_code_id' => 2,
                    'account_id' => self::ACCOUNT_ID,
                    'code' => self::CODE_2,
                    'verification_status' => VerificationCodeStatus::VERIFIED,
                    'verification_last_checked_at' => self::VERIFICATION_LAST_CHECKED_AT,
                    'update_date' => self::UPDATE_DATE + 1,
                ]
            ],
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

    public function test_messages_handle_on_store()
    {
        $message_type_id = Message::VERIFICATION_FAILED_TYPE_ID;
        $account = Account::find(self::ACCOUNT_ID);
        $verification_code = VerificationCode::find(self::ACCOUNT_ID);

        $this->assertEquals(0, count(Message::findActive($account->account_id, $message_type_id)));

        $verification_code->verification_status = VerificationCodeStatus::FAILED;

        $verification_code->store();

        // We just saved a failed verification, so should have 1 message
        $this->assertEquals(1, count(Message::findActive($account->account_id, $message_type_id)));

        $verification_code->store();

        // Current message hasn't been cleared (it's still active) so we should still have 1 active message
        $this->assertEquals(1, count(Message::findActive($account->account_id, $message_type_id)));

        $verification_code->verification_status = VerificationCodeStatus::VERIFIED;

        $verification_code->store();

        // Saving a verified ID should clear the message, resulting in zero active
        $this->assertEquals(0, count(Message::findActive($account->account_id, $message_type_id)));

        $verification_code->verification_status = VerificationCodeStatus::FAILED;

        $verification_code->store();

        // Account saved another failed code. Should have 1 active message
        $this->assertEquals(1, count(Message::findActive($account->account_id, $message_type_id)));
    }
}
