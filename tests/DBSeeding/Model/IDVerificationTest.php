<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\Model;
use Barryosull\TestingPain\DBSeeding\Model\IDVerificationStatus;
use PHPUnit\Framework\TestCase;

class IDVerificationTest extends TestCase
{
    /** @var Model\IDVerificationFinder */
    private $finder;

    /** @var Model\ShopFinder */
    private $shop_finder;

    CONST SHOP_ID = 1;
    CONST ID_TOKEN = 19;
    CONST ID_TOKEN_2 = 20;
    CONST VERIFICATION_LAST_CHECKED_AT = 1919191919;
    CONST UPDATE_DATE = 1609772649;

    public function setUp() : void {
        parent::setUp();
        $this->finder = new Model\IDVerificationFinder();
        $this->shop_finder = new Model\ShopFinder();
    }

    public function getDBSeedData(): array
    {
        return [
            'shops' => [
                [
                    'shop_id' => self::SHOP_ID,
                    'user_id' => 1,
                    'shop_shard' => 1,
                    'name' => 'shop 1',
                    'status' => 'active',
                ],
            ],
            'verifications' => [
                [
                    'verification_id' => 1,
                    'shop_id' => self::SHOP_ID,
                    'id_token' => self::ID_TOKEN,
                    'verification_status' => IDVerificationStatus::VERIFIED,
                    'verification_last_checked_at' => self::VERIFICATION_LAST_CHECKED_AT,
                    'update_date' => self::UPDATE_DATE,
                ],
                [
                    'verification_id' => 2,
                    'shop_id' => self::SHOP_ID,
                    'id_token' => self::ID_TOKEN_2,
                    'verification_status' => IDVerificationStatus::VERIFIED,
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
                    'image_url' =>  '/images/shop-tools/dashboard/notifications.svg',
                    'image_url_2' =>  '/images/shop-tools/dashboard/notifications.svg',
                    'zone' => 22,
                    'target_platform' => 1,
                ]
            ],
        ];
    }

    public function test_recordStored()
    {
        $shop = $this->shop_finder->find(self::SHOP_ID);
        $id_verification = $this->finder->find(self::SHOP_ID, 1);

        $this->assertEquals(0, count($this->fetchDisplayableCards($shop)));

        $id_verification->verification_status = IDVerificationStatus::FAILED;

        $id_verification->store();

        // We just saved a failed ID, so should have 1 card with 1 occurrence
        $this->assertEquals(1, count($this->fetchDisplayableCards($shop)));
        $this->assertEquals(1, $this->fetchDisplayableCards($shop)[0]->occurrence);

        $id_verification->store();

        // Current card hasn't been addressed (it's still displayed) so a new occurrence shouldn't be created,
        // but we should still have 1 displayable card
        $this->assertEquals(1, count($this->fetchDisplayableCards($shop)));
        $this->assertEquals(1, $this->fetchDisplayableCards($shop)[0]->occurrence);

        $id_verification->verification_status = IDVerificationStatus::VERIFIED;

        $id_verification->store();

        // Saving a verified ID should address the card, resulting in zero displayable
        $this->assertEquals(0, count($this->fetchDisplayableCards($shop)));

        $id_verification->verification_status = IDVerificationStatus::FAILED;

        $id_verification->store();

        // Shop saved another failed ID. Should have 1 displayable card, with a new occurrence
        $this->assertEquals(1, count($this->fetchDisplayableCards($shop)));
        $this->assertEquals(2, $this->fetchDisplayableCards($shop)[0]->occurrence);
    }

    private function fetchDisplayableCards($shop): array
    {
        $advisory_card_finder = new Model\AdvisoryCardFinder();
        return $advisory_card_finder->findDisplayableForShop($shop);
    }
}
