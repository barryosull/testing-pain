<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\DBSeeding\Repostiory;

use Barryosull\TestingPain\DBSeeding\AdvisoryCard\Card;
use Barryosull\TestingPain\DBSeeding\AdvisoryCard\CardFactory;
use Barryosull\TestingPain\DBSeeding\Model\IDVerification;
use Barryosull\TestingPain\DBSeeding\Model\IDVerificationStatus;
use Barryosull\TestingPain\DBSeeding\Model\Shop;
use Barryosull\TestingPain\DBSeeding\Model\ShopFinder;
use Barryosull\TestingPain\DBSeeding\Repository\IDVerificationRepository;
use Barryosull\TestingPain\DBSeeding\Repository\IDVerificationRepositoryUiUpdater;
use PHPUnit\Framework\TestCase;

class IDVerificationRepositoryUiUpdateTest extends TestCase
{
    const SHOP_ID = 1;

    private $wrapped_repo;
    private $shop_finder;
    private $card_factory;

    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $this->wrapped_repo = $this->createMock(IDVerificationRepository::class);
        $this->shop_finder = $this->createMock(ShopFinder::class);
        $this->card_factory = $this->createMock(CardFactory::class);

        $this->repo = new IDVerificationRepositoryUiUpdater($this->wrapped_repo, $this->shop_finder, $this->card_factory);
    }

    /**
     * @test
     */
    public function stores_in_wrapped_repo() {
        $this->givenShopExists();
        $id_verification = $this->makeVerificationWithStatus(IDVerificationStatus::VERIFIED);
        $this->expectVerificationToBeStoredInWrappedRepo($id_verification);

        $this->repo->store($id_verification);
    }

    /**
     * @test
     */
    public function creates_failure_card_on_failed_verification()
    {
        $shop = $this->givenShopExists();
        $id_verification = $this->makeVerificationWithStatus(IDVerificationStatus::FAILED);
        $this->expectCardToBeCreatedForShop($shop);

        $this->repo->store($id_verification);
    }

    /**
     * @test
     */
    public function addresses_failure_card_on_verified_verification()
    {
        $shop = $this->givenShopExists();
        $id_verification = $this->makeVerificationWithStatus(IDVerificationStatus::VERIFIED);
        $this->expectCardToBeAddressForShop($shop);

        $this->repo->store($id_verification);
    }

    private function makeVerificationWithStatus(string $status): IDVerification {
        $verification = $this->createMock(IDVerification::class);
        $verification->shop_id = self::SHOP_ID;
        $verification->verification_status = IDVerificationStatus::VERIFIED;
        return $verification;
    }

    private function givenShopExists(): Shop
    {
        $shop = $this->createMock(Shop::class);
        $shop->id = self::SHOP_ID;
        $this->shop_finder->method('find')
            ->willReturn($shop);
        return $shop;
    }

    private function expectVerificationToBeStoredInWrappedRepo(IDVerification $verification)
    {
        $this->wrapped_repo->expects($this->once())
            ->method('store')
            ->with($verification);
    }

    private function expectCardToBeCreatedForShop(Shop $shop)
    {
        $card = $this->createMock(Card::class);
        $this->card_factory->method('make')
            ->with($shop->shop_id)
            ->willReturn($card);

        $card->expects($this->once())
            ->method('createForShop')
            ->with($shop);
    }

    private function expectCardToBeAddressForShop(Shop $shop)
    {
        $card = $this->createMock(Card::class);
        $this->card_factory->method('make')
            ->with($shop->shop_id)
            ->willReturn($card);

        $card->expects($this->once())
            ->method('markAsAddressed')
            ->with($shop);
    }
}
