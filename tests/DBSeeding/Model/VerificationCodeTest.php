<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\Model;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCodeStatus;
use PHPUnit\Framework\TestCase;

class VerificationCodeTest extends TestCase
{
    /** @var Model\VerificationCodeFinder */
    private $finder;

    /** @var Model\AccountFinder */
    private $account_finder;

    CONST ACCOUNT_ID = 1;
    CONST CODE = 1111;
    CONST CODE_2 = 2222;
    CONST VERIFICATION_LAST_CHECKED_AT = 1919191919;
    CONST UPDATE_DATE = 1609000000;


    public function setUp() : void
    {
        parent::setUp();
        $this->finder = new Model\VerificationCodeFinder();
        $this->account_finder = new Model\AccountFinder();
    }

    public function getDBSeedData(): array
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
            'verifications' => [
                [
                    'verification_id' => 1,
                    'account_id' => self::ACCOUNT_ID,
                    'code' => self::CODE,
                    'verification_status' => VerificationCodeStatus::VERIFIED,
                    'verification_last_checked_at' => self::VERIFICATION_LAST_CHECKED_AT,
                    'update_date' => self::UPDATE_DATE,
                ],
                [
                    'verification_id' => 2,
                    'account_id' => self::ACCOUNT_ID,
                    'code' => self::CODE_2,
                    'verification_status' => VerificationCodeStatus::VERIFIED,
                    'verification_last_checked_at' => self::VERIFICATION_LAST_CHECKED_AT,
                    'update_date' => self::UPDATE_DATE + 1,
                ]
            ],
            'advisory_card' => [
                [
                    'advisory_card_id' =>  1,
                    'next_advisory_card_id' =>  null,
                    'next_advisory_card_delay' =>  0,
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

    public function test_recordStored()
    {
        $account = $this->account_finder->find(self::ACCOUNT_ID);
        $verification_code = $this->finder->find(self::ACCOUNT_ID);

        $this->assertEquals(0, count($this->fetchDisplayableCards($account)));

        $verification_code->verification_status = VerificationCodeStatus::FAILED;

        $verification_code->store();

        // We just saved a failed ID, so should have 1 card with 1 occurrence
        $this->assertEquals(1, count($this->fetchDisplayableCards($account)));
        $this->assertEquals(1, $this->fetchDisplayableCards($account)[0]->occurrence);

        $verification_code->store();

        // Current card hasn't been addressed (it's still displayed) so a new occurrence shouldn't be created,
        // but we should still have 1 displayable card
        $this->assertEquals(1, count($this->fetchDisplayableCards($account)));
        $this->assertEquals(1, $this->fetchDisplayableCards($account)[0]->occurrence);

        $verification_code->verification_status = VerificationCodeStatus::VERIFIED;

        $verification_code->store();

        // Saving a verified ID should address the card, resulting in zero displayable
        $this->assertEquals(0, count($this->fetchDisplayableCards($account)));

        $verification_code->verification_status = VerificationCodeStatus::FAILED;

        $verification_code->store();

        // Account saved another failed code. Should have 1 displayable card, with a new occurrence
        $this->assertEquals(1, count($this->fetchDisplayableCards($account)));
        $this->assertEquals(2, $this->fetchDisplayableCards($account)[0]->occurrence);
    }

    private function fetchDisplayableCards($account): array
    {
        $advisory_card_finder = new Model\AdvisoryCardFinder();
        return $advisory_card_finder->findDisplayableForAccount($account);
    }
}
