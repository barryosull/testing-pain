<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\Legibility\HTTP;

use PHPUnit\Framework\TestCase;
use Barryosull\TestingPainTests\Legibility\Api;
use Barryosull\TestingPainTests\Legibility\DbSeeder;
use Barryosull\TestingPainTests\Legibility\FakeSynchronousApiClient;
use Barryosull\TestingPainTests\Legibility\TestingConfig;
use Barryosull\TestingPainTests\Legibility\TestingSpyConstructorOverloader;
use Barryosull\TestingPain\Legibility\Command\CreateIdentityForCurrentYear;
use Barryosull\TestingPain\Legibility\HTTP\IdentityController;
use Barryosull\TestingPain\Legibility\Model\OwnerFinder;
use Barryosull\TestingPain\Legibility\Model\Shop;
use Barryosull\TestingPain\Legibility\Model\ShopFinder;
use Barryosull\TestingPain\Legibility\Service\ApiClient;
use Barryosull\TestingPain\Legibility\Service\OnboardingService;
use Barryosull\TestingPain\Legibility\HTTP;

class IdentityControllerTest extends TestCase
{
    const INDIVIDUAL_SHOP_ID = 1;
    const BUSINESS_SHOP_ID = 2;

    const SHOPS = [
        self::INDIVIDUAL_SHOP_ID => 'IndividualShop',
        self::BUSINESS_SHOP_ID => 'BusinessShop',
    ];

    const UNSUPPORTED_BUSINESS_COUNTRY_ID = 212;

    private $onboarding_service;
    private $api_client;
    private $create_identity_command;

    public function setUp() : void
    {
        parent::setUp();

        $this->onboarding_service = $this->makeOnboardingService();
        $this->api_client = $this->makeApiClient();
        $this->create_identity_command = 
            $this->makeCreateIdentityForCurrentYearCommand();

        $this->givenShopsExist();
        $this->givenUsersExist();
        $this->givenNoUserAddresses();
        $this->givenNoOwners();
        $this->givenInputAndIdentityAreValid();
        $this->givenAddressConfirmationIsRequired();
        $this->givenCountriesAreExcludedFromAddressConfirmation();
    }

    public function tearDown() : void
    {
        TestingConfig::reset();
    }

    public function test_400_badRequestOnMissingRequestDetails()
    {
        $input_array = $this->makeHttpInputArray(['is_business' => true]);

        $this->expectException(HTTP\BadRequestError::class);

        $this->whenIdentityIsSubmitted(self::BUSINESS_SHOP_ID, $input_array);
    }

    public function test_400_badRequestIfCountryRequiresJurisdictionButNotProvided()
    {
        $input_array = $this->getMakeHttpInputArrayWithMissingJurisdiction();

        $this->expectException(HTTP\BadRequestError::class);

        $this->whenIdentityIsSubmitted(self::BUSINESS_SHOP_ID, $input_array);
    }

    public function test_400_badRequestIfCountryRequiresIdButNotProvided()
    {
        $input_array = $this->getMakeHttpInputArrayWithMissingId();

        $this->expectException(HTTP\BadRequestError::class);

        $this->whenIdentityIsSubmitted(self::BUSINESS_SHOP_ID, $input_array);
    }

    public function test_400_badRequestWhenInvalidBusinessAndCountryIDUsed()
    {
        $http_input = $this->makeHttpInputArrayWithInvalidCountyId();

        $this->expectException(HTTP\BadRequestError::class);
        $this->expectIdentityNotToBeCreatedForYear();

        $this->whenIdentityIsSubmitted(self::BUSINESS_SHOP_ID, $http_input);
    }

    public function test_200_setsCorrectIdentityDetailsForIndividual_noIndividualIdRequired()
    {
        $this->givenIdsAreNotRequired();

        $this->whenIdentityIsSubmitted(self::INDIVIDUAL_SHOP_ID, $this->makeHttpInputArray());

        $this->verifyShopIsIndividual(self::INDIVIDUAL_SHOP_ID);
        $this->verifyShopAddressIsIndividuals(self::INDIVIDUAL_SHOP_ID);
    }

    public function test_200_setsCorrectIdentityDetailsWithAddressConfirmationDisabled_noIndividualIdRequired()
    {
        $this->givenIdsAreNotRequired();
        $this->givenAddressConfirmationIsNotRequired();

        $this->expectIdentityNotToBeCreatedForYear();

        $this->whenIdentityIsSubmitted(self::INDIVIDUAL_SHOP_ID, $this->makeHttpInputArray());

        $this->verifyShopIsIndividual(self::INDIVIDUAL_SHOP_ID);
        $this->verifyShopAddressIsIndividuals(self::INDIVIDUAL_SHOP_ID);
    }

    public function test_200_setsCorrectIdentityDetailsForIndividual_individualIdRequired()
    {
        $this->givenIdsAreRequired();

        $this->expectIdentityToBeCreatedForYear();

        $this->whenIdentityIsSubmitted(self::INDIVIDUAL_SHOP_ID, $this->makeHttpInputArray());

        $this->verifyShopIsIndividual(self::INDIVIDUAL_SHOP_ID);
        $this->verifyShopAddressIsIndividuals(self::INDIVIDUAL_SHOP_ID);
    }

    public function test_200_setsCorrectBusinessIdentityDetailsForBusiness()
    {
        $http_input = $this->makeHttpInputArrayForBusiness();

        $this->expectIdentityToBeCreatedForYear();

        $this->whenIdentityIsSubmitted(self::BUSINESS_SHOP_ID, $http_input);

        $this->verifyShopIsBusiness(self::BUSINESS_SHOP_ID);
        $this->verifyShopAddressIsBusinesses(self::BUSINESS_SHOP_ID);
        $this->verifyOwnersWereAdded(self::BUSINESS_SHOP_ID);
    }

    public function test_200_setsCorrectBusinessIdentityDetailsForNonUSBusiness()
    {
        $non_us_business_identity = $this->makeNonUsBusinessIdentity();
        $http_input = $this->makeHttpInputArrayForBusiness($non_us_business_identity);

        $this->expectIdentityNotToBeCreatedForYear();

        $this->whenIdentityIsSubmitted(self::BUSINESS_SHOP_ID, $http_input);

        $this->verifyShopIsBusiness(self::BUSINESS_SHOP_ID);
        $this->verifyShopAddressIsTakenFromBusinessIdentity(self::BUSINESS_SHOP_ID, $non_us_business_identity['address']);
        $this->verifyOwnersWereAdded(self::BUSINESS_SHOP_ID);
    }

    public function test_200_PersonsNotOverwriteAddressOnQuestionsAnswered()
    {
        $http_input = $this->makeHttpInputArrayForBusiness();

        $this->expectIdentityToBeCreatedForYear();

        $this->whenIdentityIsSubmitted(self::BUSINESS_SHOP_ID, $http_input);

        $this->verifyShopAddressNameIsBusinessName(self::BUSINESS_SHOP_ID);
    }

    public function test_addressArrayContainsIdIfItsInTheInput()
    {
        $input = (new HTTP\Input())->newInputWithData($this->makeHttpInputArray());
        $address = IdentityController::makeAddressArrayFromInput($input);
        $this->assertArrayHasKey('id', $address);
    }


    //********************************************
    // When
    //********************************************

    private function whenIdentityIsSubmitted(int $shop_id, array $http_input_array): void
    {
        $spy = new TestingSpyConstructorOverloader();

        $spy->overload(OnboardingService::class, $this->onboarding_service);
        $spy->overload(ApiClient::class, $this->api_client, false);
        $spy->overload(CreateIdentityForCurrentYear::class, $this->create_identity_command);

        Api::callShop($shop_id, IdentityController::class, $http_input_array);

        $spy->restore();
    }


    //********************************************
    // Factory methods
    //********************************************

    private function makeHttpInputArray(array $http_input_overrides = [])
    {
        return array_merge([
            'firstname' => 'Owner',
            'last_name' => 'Person',
            'day' => 10,
            'month' => 5,
            'year' => 1991,
            'street_name' => ' St',
            'street_number' => '11',
            'city' => 'San Francisco',
            'state' => 'CA',
            'zip' => '94111',
            'country_id' => 209,
            'phone' => '111-222-3333',
            'id' => '1234',
            'full_id' => '11-222-3333',
        ], $http_input_overrides);
    }

    private function makeOwnerInput(array $values_per_owner = [[]])
    {
        return array_map(function($values) {
            return array_merge([
                'owners_id' => null,
                'birthday_month'      => 2,
                'birthday_year'       => 1991,
                'birthday_day'        => 20,
                'name'                => 'Owner Person',
                'last_four_id'       => '2222',
                'primary'             => true,
                'relationship'        => 'owner',
                'address'             => [
                    'name' => 'Owner Person',
                    'first_line' => '22  St',
                    'city' => 'San Francisco',
                    'state' => 'CA',
                    'zip' => '94111',
                    'country_id' => 209,
                    'phone' => '111-222-3333',
                ]
            ], $values);
        }, $values_per_owner);
    }

    private function makeBusinessIdentityInput(array $values = [])
    {
        return array_merge([
            'address' => [
                'name' => 'Shop Business Address',
                'first_line' => '33  St',
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

    protected function getMakeHttpInputArrayWithMissingJurisdiction(): array
    {
        return $this->makeHttpInputArray([
            'is_business' => true,
            'owners' => $this->makeOwnerInput(),
            'business_identity' => $this->makeBusinessIdentityInput([
                'address' => [
                    'name' => 'Shop Business Address',
                    'first_line' => '123 Fake St',
                    'city' => 'San Francisco',
                    'state' => 'CA',
                    'zip' => '94111',
                    'country_id' => 209,
                    'phone' => '718-855-7955',
                ],
                'business_registration_number' => '123456789',
                'jurisdiction' => null,
                'id' => '12-1234567',
            ]),
        ]);
    }

    protected function getMakeHttpInputArrayWithMissingId(): array
    {
        return $this->makeHttpInputArray([
            'is_business' => true,
            'owners' => $this->makeOwnerInput(),
            'business_identity' => $this->makeBusinessIdentityInput([
                'address' => [
                    'name' => 'Shop Business Address',
                    'first_line' => '123 Fake St',
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
        ]);
    }

    protected function makeHttpInputArrayWithInvalidCountyId(): array
    {
        return $this->makeHttpInputArray([
            'is_business' => true,
            'owners' => $this->makeOwnerInput(),
            'business_identity' => $this->makeBusinessIdentityInput(),
            'country_id' => self::UNSUPPORTED_BUSINESS_COUNTRY_ID,
        ]);
    }

    private function makeNonUsBusinessIdentity(): array
    {
        return [
            'address' => [
                'name' => 'Shop Business Address',
                'first_line' => '123 Germany St',
                'city' => 'Berlin',
                'state' => '',
                'zip' => 'abc123',
                'country_id' => 91,
                'phone' => '111-222-3333',
            ],
            'business_registration_number' => '123456789',
            'jurisdiction' => 'AB',
        ];
    }

    private function makeHttpInputArrayForBusiness(array $business_identity = []): array
    {
        return $this->makeHttpInputArray([
            'is_business' => true,
            'owners' => $this->makeOwnerInput(),
            'business_identity' => $this->makeBusinessIdentityInput($business_identity),
        ]);
    }

    private function makeCreateIdentityForCurrentYearCommand()
    {
        return $this->createMock(CreateIdentityForCurrentYear::class);
    }

    private function makeOnboardingService()
    {
        return $this->createPartialMock(
            OnboardingService::class,
            [
                'validateOnboardingEligibility',
                'validateAddress',
                'submitPromptAnswers',
                'verifyIdentity',
                'setOnboardingFields',
            ]
        );
    }

    private function makeApiClient()
    {
        return FakeSynchronousApiClient::create($this);
    }


    //********************************************
    // Given
    //********************************************

    private function givenShopsExist(): void
    {
        $shops = [];
        foreach (self::SHOPS as $shop_id => $name) {
            $shops[] = [
                'shop_id' => $shop_id,
                'user_id' => $shop_id,
                'status' => 'active',
                'status_date' => 0,
                'address_id' => 0,
                'banner_id' => 0,
                'city' => 'Brooklyn',
                'region' => 'New York',
                'name' => $name,
            ];
        }
        DbSeeder::seed(['shops' => $shops]);
    }

    private function givenUsersExist(): void
    {
        $users = [];
        foreach (self::SHOPS as $shop_id => $name) {
            $users[] = [
                'user_id' => $shop_id,
                'login_name' => "test$shop_id",
                'first_name' => 'Test',
                'primary_email' => "test$shop_id@example.com"
            ];
        }
        DbSeeder::seed(['users' => $users]);
    }

    private function givenNoUserAddresses(): void
    {
        DbSeeder::seed(['users_addresses' => []]);
    }

    private function givenNoOwners(): void
    {
        DbSeeder::seed(['owners' => []]);
    }

    private function givenInputAndIdentityAreValid(): void
    {
        $this->onboarding_service->method('validateAddress')
            ->willReturn([true, null]);
        $this->onboarding_service->method('validateOnboardingEligibility')
            ->willReturn(true);
        $this->onboarding_service->method('verifyIdentity')
            ->willReturn([
                'result_code' => 'PASS',
            ]);
        $this->onboarding_service->method('submitPromptAnswers')
            ->willReturn(true);
    }

    private function givenCountriesAreExcludedFromAddressConfirmation(): void
    {
        TestingConfig::setFeatureData('address_confirm', [
            'excluded_countries' => [self::UNSUPPORTED_BUSINESS_COUNTRY_ID]
        ]);
    }

    private function givenAddressConfirmationIsNotRequired(): void
    {
        TestingConfig::disableFeature("address_confirm");
    }

    private function givenAddressConfirmationIsRequired(): void
    {
        TestingConfig::enableFeature('address_confirm');
    }

    private function givenIdsAreRequired()
    {
        $this->configureIfIdsAreRequired($are_ids_required = true);
    }

    private function givenIdsAreNotRequired()
    {
        $this->configureIfIdsAreRequired($are_ids_required = false);
    }

    private function configureIfIdsAreRequired(bool $are_ids_required)
    {
        $enabled = ($are_ids_required) ? 100 : 0;
        TestingConfig::setForTest(
            [
                'config' => [
                    'ids' => [
                        'ids_required' => [
                            'enabled' => $enabled,
                        ]
                    ],
                ],
            ]
        );
    }


    //********************************************
    // Expectations
    //********************************************

    private function expectIdentityToBeCreatedForYear(): void
    {
        $this->create_identity_command->expects($this->once())->method('run');
    }

    private function expectIdentityNotToBeCreatedForYear(): void
    {
        $this->create_identity_command->expects($this->never())->method('run');
    }


    //********************************************
    // Verifications (query + assertion)
    //********************************************

    private function verifyShopIsIndividual(int $shop_id): void
    {
        $shop = $this->getShop($shop_id);
        $this->assertFalse($shop->getIsBusiness(), 'Shop should be marked as an individual');
    }

    private function verifyShopAddressIsIndividuals(int $shop_id): void
    {
        $shop = $this->getShop($shop_id);
        $address = $shop->address();
        $this->assertEquals('11  St', $address['first_line'], 'Shop address uses the individual identity');
        $this->assertEquals('Owner Person', $address['name'], 'Shop address uses the individual identity');
    }

    private function verifyShopIsBusiness(int $shop_id): void
    {
        $shop = $this->getShop($shop_id);
        $this->assertTrue($shop->getIsBusiness(), 'Shop should be marked as a business');
    }

    private function verifyShopAddressIsBusinesses(int $shop_id): void
    {
        $shop = $this->getShop($shop_id);
        $address = $shop->address();
        $this->assertEquals('33  St', $address['first_line'], 'Shop address uses the business identity');
        $this->assertEquals('Shop Business Address', $address['name'], 'Shop address uses the business identity');
    }

    private function verifyOwnersWereAdded(int $shop_id): void
    {
        $owner_names = $this->getOwnerNames($shop_id);
        $expected_owner_names = [['owner' => 'Owner Person']];
        $this->assertEqualsCanonicalizing($expected_owner_names, $owner_names, 'Owners should have been added');
    }

    private function verifyShopAddressIsTakenFromBusinessIdentity(int $shop_id, array $business_identity_address): void
    {
        $shop = $this->getShop($shop_id);
        $address = $shop->address();
        $this->assertEquals($business_identity_address['first_line'], $address['first_line'], 'Shop address uses the business identity');
        $this->assertEquals($business_identity_address['name'], $address['name'], 'Shop address uses the business identity');
    }

    private function verifyShopAddressNameIsBusinessName(int $shop_id): void
    {
        $shop = $this->getShop($shop_id);
        $address = $shop->address();
        $this->assertEquals('Shop Business Address', $address->name, 'Shop address uses the business identity');
    }


    //********************************************
    // Queries
    //********************************************

    private function getShop(int $shop_id): Shop
    {
        $shop_finder = new ShopFinder();
        return $shop_finder->find($shop_id);
    }

    private function getOwnerNames(int $shop_id): array
    {
        $owners_finder = new OwnerFinder();
        $owners = $owners_finder->findAllForShopId($shop_id);
        return array_map(function ($owner) {
            return ['name' => $owner['name']];
        }, $owners);
    }
}
