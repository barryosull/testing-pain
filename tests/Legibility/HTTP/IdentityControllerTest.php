<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\Legibility\HTTP;

use Barryosull\TestingPain\Legibility\Command\CreateIdentityForCurrentYear;
use Barryosull\TestingPain\Legibility\HTTP\IdentityController;
use Barryosull\TestingPain\Legibility\Model\OwnerFinder;
use Barryosull\TestingPain\Legibility\Model\ShopFinder;
use Barryosull\TestingPain\Legibility\Service\ApiClient;
use Barryosull\TestingPain\Legibility\Service\OnboardingService;
use Barryosull\TestingPainTests\Legibility\Api;
use Barryosull\TestingPainTests\Legibility\DbSeeder;
use Barryosull\TestingPainTests\Legibility\Request\FakeSynchronousApiClient;
use Barryosull\TestingPainTests\Legibility\TestingConfig;
use Barryosull\TestingPainTests\Legibility\TestingSpyConstructorOverloader;
use PHPUnit\Framework\TestCase;
use Barryosull\TestingPain\Legibility\HTTP;

class IdentityControllerTest extends TestCase
{
    const INDIVIDUAL_SHOP_ID = 1;
    const BUSINESS_SHOP_ID = 2;

    const UNSUPPORTED_BUSINESS_COUNTRY_ID = 212;

    const SHOPS = [
        self::INDIVIDUAL_SHOP_ID => 'IndividualShop',
        self::BUSINESS_SHOP_ID => 'BusinessShop',
    ];

    public function seedData()
    {
        $config = [
            'shops' => [],
            'users' => [],
            'users_addresses' => [],
            'owners' => []
        ];

        foreach (self::SHOPS as $shop_id => $name) {
            array_push($config['users'], [
                'user_id' => $shop_id,
                'login_name' => "test$shop_id",
                'first_name' => 'Test',
                'primary_email' => "test$shop_id@example.com"
            ]);
            array_push($config['shops'], [
                'shop_id' => $shop_id,
                'user_id' => $shop_id,
                'status' => 'active',
                'status_date' => 0,
                'address_id' => 0,
                'banner_id' => 0,
                'structured_policies_id' => 1,
                'city' => 'Brooklyn',
                'region' => 'New York',
                'name' => $name,
            ]);
        }

        DbSeeder::seed($config);
    }

    public function setUp() : void
    {
        parent::setUp();

        $this->seedData();

        TestingConfig::enableFeature('address_confirm');
        TestingConfig::setFeatureData('address_confirm', [
            'excluded_countries' => [self::UNSUPPORTED_BUSINESS_COUNTRY_ID]
        ]);

        $this->spy = new TestingSpyConstructorOverloader();
        $this->api_client = FakeSynchronousApiClient::create($this);
        $this->spy->overload(
            ApiClient::class,
            $this->api_client,
            false
        );

        $this->onboarding_service = $this->createPartialMock(
            OnboardingService::class,
            [
                'validateAddress',
                'verifyIdentity',
                'validateOnboardingEligibility',
                'setOnboardingFields',
                'submitPromptAnswers'
            ]
        );
        $this->onboarding_service->expects($this->any())
            ->method('validateAddress')
            ->willReturn([true, null]);
        $this->onboarding_service->expects($this->any())
            ->method('validateOnboardingEligibility')
            ->willReturn(true);
        $this->onboarding_service->expects($this->any())
            ->method('verifyIdentity')
            ->willReturn([
                'result_code' => 'PASS',
            ]);
        $this->onboarding_service->expects($this->any())
            ->method('submitPromptAnswers')
            ->willReturn(true);
        $this->spy->overload(
            OnboardingService::class,
            $this->onboarding_service
        );
    }

    public function tearDown() : void
    {
        $this->spy->restore();
        TestingConfig::reset();
    }

    private function createInput(array $values = []): array
    {
        return array_merge([
            'firstname' => 'Owner',
            'last_name' => 'Person',
            'day' => 10,
            'month' => 5,
            'year' => 1991,
            'street_name' => 'Fake St',
            'street_number' => '11',
            'city' => 'San Francisco',
            'state' => 'CA',
            'zip' => '94111',
            'country_id' => 209,
            'phone' => '111-222-3333',
            'id' => '1234',
            'full_id' => '11-222-3333',
        ], $values);
    }

    private function createOwnerInput(array $values_per_owner = [[]]): array
    {
        return array_map(function($values) {
            return array_merge([
                'owner_id' => null,
                'birthday_month'      => 2,
                'birthday_year'       => 1991,
                'birthday_day'        => 20,
                'name'                => 'Owner Person',
                'last_four_id'       => '2222',
                'primary'             => true,
                'relationship'        => 'owner',
                'address'             => [
                    'name' => 'Owner Person',
                    'first_line' => '22 Fake St',
                    'city' => 'San Francisco',
                    'state' => 'CA',
                    'zip' => '94111',
                    'country_id' => 209,
                    'phone' => '111-222-3333',
                ]
            ], $values);
        }, $values_per_owner);
    }

    private function createBusinessIdentityInput(array $values = [])
    {
        return array_merge([
            'address' => [
                'name' => 'Shop Business Address',
                'first_line' => '33 Fake St',
                'city' => 'San Francisco',
                'state' => 'CA',
                'zip' => '94111',
                'country_id' => 209,
                'phone' => '111-222-3333',
            ],
            'business_registration_number' => '123456789',
            'jurisdiction' => 'CA',
            'id' => '11-2222222'
        ], $values);
    }

    public function test400BadRequestOnMissingRequestDetails()
    {
        $this->expectException(HTTP\BadRequestError::class);
        Api::callShop(
            self::BUSINESS_SHOP_ID,
            IdentityController::class,
            $this->createInput([ 'is_business' => true ])
        );
    }

    public function test400BadRequestIfCountryRequiresJurisdictionButNotProvided()
    {
        $this->expectException(HTTP\BadRequestError::class);
        Api::callShop(
            self::BUSINESS_SHOP_ID,
            IdentityController::class,
            $this->createInput([
                'is_business' => true,
                'owners' => $this->createOwnerInput(),
                'business_identity' => $this->createBusinessIdentityInput([
                    'address' => [
                        'name' => 'Shop Business Address',
                        'first_line' => '33 Fake St',
                        'city' => 'San Francisco',
                        'state' => 'CA',
                        'zip' => '94111',
                        'country_id' => 209,
                        'phone' => '111-222-3333',
                    ],
                    'business_registration_number' => '123456789',
                    'jurisdiction' => null,
                    'id' => '11-2222222',
                ]),
            ])
        );
    }

    public function test400BadRequestIfCountryRequiresIdButNotProvided()
    {
        $this->expectException(HTTP\BadRequestError::class);
        Api::callShop(
            self::BUSINESS_SHOP_ID,
            IdentityController::class,
            $this->createInput([
                'is_business' => true,
                'owners' => $this->createOwnerInput(),
                'business_identity' => $this->createBusinessIdentityInput([
                    'address' => [
                        'name' => 'Shop Business Address',
                        'first_line' => '33 Fake St',
                        'city' => 'San Francisco',
                        'state' => 'CA',
                        'zip' => '94111',
                        'country_id' => 209,
                        'phone' => '111-222-3333',
                    ],
                    'business_registration_number' => '123456789',
                    'jurisdiction' => 'CA',
                    'id' => null,
                ]),
            ])
        );
    }

    public function test200OKSetsCorrectIdentityDetailsForIndividual_noIndividualIdRequired()
    {
        $this->configureIfIDsAreRequired($are_ids_required = false);
        Api::callShop(
            self::INDIVIDUAL_SHOP_ID,
            IdentityController::class,
            $this->createInput()
        );

        $shop_finder = new ShopFinder();
        $shop = $shop_finder->find(self::INDIVIDUAL_SHOP_ID);

        $this->assertFalse(
            $shop->getIsBusiness(),
            'Shop should be marked as an individual'
        );

        $address = $shop->address();
        $this->assertEquals(
            '11 Fake St',
            $address['first_line'],
            'Shop address uses the individual identity'
        );
        $this->assertEquals(
            'Owner Person',
            $address['name'],
            'Shop address uses the individual identity'
        );
    }

    public function test_200OKSetsCorrectIdentityDetailsWithAddressConfirmDisabled_noIndividualIdRequired()
    {
        $this->configureIfIDsAreRequired($are_ids_required = false);
        TestingConfig::disableFeature("address_confirm");

        $create_identity_command = $this->createMock(CreateIdentityForCurrentYear::class);
        $create_identity_command->expects($this->never())
            ->method('run');
        $this->spy->overload(CreateIdentityForCurrentYear::class, $create_identity_command);

        Api::callShop(
            self::INDIVIDUAL_SHOP_ID,
            IdentityController::class,
            $this->createInput()
        );

        $shop_finder = new ShopFinder();
        $shop = $shop_finder->find(self::INDIVIDUAL_SHOP_ID);

        $this->assertFalse(
            $shop->getIsBusiness(),
            'Shop should be marked as an individual'
        );

        $address = $shop->address();
        $this->assertEquals(
            '11 Fake St',
            $address['first_line'],
            'Shop address uses the individual identity'
        );
        $this->assertEquals(
            'Owner Person',
            $address['name'],
            'Shop address uses the individual identity'
        );
    }

    public function test_200OKSetsCorrectIdentityDetailsForIndividual_individualIdRequired()
    {
        $this->configureIfIDsAreRequired($are_ids_required = true);

        $create_identity_command = $this->createMock(CreateIdentityForCurrentYear::class);
        $create_identity_command->expects($this->once())
            ->method('run');
        $this->spy->overload(CreateIdentityForCurrentYear::class, $create_identity_command);

        Api::callShop(
            self::INDIVIDUAL_SHOP_ID,
            IdentityController::class,
            $this->createInput()
        );

        $shop_finder = new ShopFinder();
        $shop = $shop_finder->find(self::INDIVIDUAL_SHOP_ID);

        $this->assertFalse(
            $shop->getIsBusiness(),
            'Shop should be marked as an individual'
        );

        $address = $shop->address();
        $this->assertEquals(
            '11 Fake St',
            $address['first_line'],
            'Shop address uses the individual identity'
        );
        $this->assertEquals(
            'Owner Person',
            $address['name'],
            'Shop address uses the individual identity'
        );
    }

    public function test_200OKSetsCorrectBusinessIdentityDetailsForBusiness()
    {
        $create_identity_command = $this->createMock(CreateIdentityForCurrentYear::class);
        $create_identity_command->expects($this->once())
            ->method('run');
        $this->spy->overload(CreateIdentityForCurrentYear::class, $create_identity_command);

        Api::callShop(
            self::BUSINESS_SHOP_ID,
            IdentityController::class,
            $this->createInput([
                'is_business' => true,
                'owners' => $this->createOwnerInput(),
                'business_identity' => $this->createBusinessIdentityInput(),
            ])
        );

        $shop_finder = new ShopFinder();
        $shop = $shop_finder->find(self::BUSINESS_SHOP_ID);

        $this->assertTrue(
            $shop->getIsBusiness(),
            'Shop should be marked as a business'
        );

        $address = $shop->address();
        $this->assertEquals(
            '33 Fake St',
            $address['first_line'],
            'Shop address uses the business identity'
        );
        $this->assertEquals(
            'Shop Business Address',
            $address['name'],
            'Shop address uses the business identity'
        );

        $owners_finder = new OwnerFinder();
        $owners = $owners_finder->findAllForShopId(self::BUSINESS_SHOP_ID);
        $this->assertEqualsCanonicalizing(
            [['owner' => 'Business Person']],
            array_map(function($owner) {
                return ['name' => $owner['name']];
            }, $owners),
            'Owners should have been added'
        );
    }

    public function test_200OKSetsCorrectBusinessIdentityDetailsForNonUSBusiness()
    {
        $create_identity_command = $this->createMock(CreateIdentityForCurrentYear::class);
        $create_identity_command->expects($this->never())
            ->method('run');
        $this->spy->overload(CreateIdentityForCurrentYear::class, $create_identity_command);

        $business_identity = [
            'address' => [
                'name' => 'Shop Business Address',
                'first_line' => '33 Germany St',
                'city' => 'Berlin',
                'state' => '',
                'zip' => 'abc123',
                'country_id' => 91,
                'phone' => '718-855-7955',
            ],
            'business_registration_number' => '123456789',
            'jurisdiction' => 'AB',
        ];

        Api::callShop(
            self::BUSINESS_SHOP_ID,
            IdentityController::class,
            $this->createInput([
                'is_business' => true,
                'owners' => $this->createOwnerInput(),
                'business_identity' => $this->createBusinessIdentityInput($business_identity),
            ])
        );

        $shop_finder = new ShopFinder();
        $shop = $shop_finder->find(self::BUSINESS_SHOP_ID);

        $this->assertTrue(
            $shop->getIsBusiness(),
            'Shop should be marked as a business'
        );

        $address = $shop->address();
        $this->assertEquals(
            $business_identity['address']['first_line'],
            $address['first_line'],
            'Shop address uses the business identity'
        );
        $this->assertEquals(
            'Shop Business Address',
            $address['name'],
            'Shop address uses the business identity'
        );

        $owners_finder = new OwnerFinder();
        $owners = $owners_finder->findAllForShopId(self::BUSINESS_SHOP_ID);
        $this->assertEqualsCanonicalizing(
            [['owner' => 'Owner Person']],
            array_map(function($owner) {
                return ['name' => $owner['name']];
            }, $owners),
            'Owners should have been added'
        );
    }

    public function test_400BadRequestWhenInvalidBusinessAndBankCountryIDUsed()
    {
        $this->expectException(HTTP\BadRequestError::class);

        $create_identity_command = $this->createMock(CreateIdentityForCurrentYear::class);
        $create_identity_command->expects($this->never())
            ->method('run');
        $this->spy->overload(CreateIdentityForCurrentYear::class, $create_identity_command);

        Api::callShop(
            self::BUSINESS_SHOP_ID,
            IdentityController::class,
            $this->createInput([
                'is_business' => true,
                'owners' => $this->createOwnerInput(),
                'business_identity' => $this->createBusinessIdentityInput(),
                'country_id' => self::UNSUPPORTED_BUSINESS_COUNTRY_ID,
            ])
        );
    }

    public function test_200OkDoesNotOverwriteAddressOnQuestionsAnswered()
    {
        TestingConfig::enableFeature('address_confirm');

        $create_identity_command = $this->createMock(CreateIdentityForCurrentYear::class);
        $create_identity_command->expects($this->once())
            ->method('run');
        $this->spy->overload(CreateIdentityForCurrentYear::class, $create_identity_command);

        Api::callShop(
            self::BUSINESS_SHOP_ID,
            IdentityController::class,
            $this->createInput([
                'is_business' => true,
                'owners' => $this->createOwnerInput(),
                'business_identity' => $this->createBusinessIdentityInput(),
            ])
        );

        Api::callShop(
            self::BUSINESS_SHOP_ID,
            IdentityController::class,
            $this->createInput([
                'is_completed_business' => true,
                'prompt_answers' => [],
                'log_id' => 1,
            ])
        );

        $shop_finder = new ShopFinder();
        $shop = $shop_finder->find(self::BUSINESS_SHOP_ID);

        $this->assertEquals(
            'Shop Business Address',
            $shop->Address()->name,
            'Shop address uses the business identity'
        );
    }

    public function test_AddressArrayContainsIdIfItsInTheInput()
    {
        $input = (new HTTP\Input())->newInputWithData($this->createInput());
        $address = IdentityController::makeAddressArrayFromInput($input);
        $this->assertArrayHasKey('id', $address);
    }

    private function configureIfIDsAreRequired(bool $are_ids_required)
    {
        $enabled = ($are_ids_required) ? 100 : 0;
        TestingConfig::setForTest([
            'config' => [
                'ids' => [
                    'verify_ids_during_onboarding' => [
                        'enabled' => $enabled,
                    ]
                ]
            ],
        ]);
    }
}
