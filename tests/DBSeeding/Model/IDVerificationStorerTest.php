<?php

namespace Barryosull\TestingPainTests\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\AdvisoryCard\Card;
use Barryosull\TestingPain\DBSeeding\AdvisoryCard\CardFactory;
use Barryosull\TestingPain\DBSeeding\Model\IDVerification;
use Barryosull\TestingPain\DBSeeding\Model\IDVerificationStatus;
use Barryosull\TestingPain\DBSeeding\Model\IDVerificationStorer;
use Barryosull\TestingPain\DBSeeding\Model\Shop;
use Barryosull\TestingPain\DBSeeding\Model\ShopFinder;
use PHPUnit\Framework\TestCase;

class IDVerificationStorerTest extends TestCase
{
    const SHOP_ID = 1;

    /** @var ShopFinder */
    private $shop_finder;
    /** @var CardFactory */
    private $card_factory;

    private $storer;

    public function setUp(): void
    {
        parent::setUp();

        $this->shop_finder = $this->createMock(ShopFinder::class);
        $this->card_factory = $this->createMock(CardFactory::class);

        $this->storer = new IDVerificationStorer($this->shop_finder, $this->card_factory);
    }

    /**
     * @test
     */
    public function stores_id_verification()
    {
        $this->givenShopExists();
        $id_verification = $this->makeVerificationWithStatus(IDVerificationStatus::VERIFIED);
        $this->expectVerificationToBeStored($id_verification);

        $this->storer->store($id_verification);
    }

    /**
     * @test
     */
    public function creates_failure_card_on_failed_verification()
    {
        $shop = $this->givenShopExists();
        $id_verification = $this->makeVerificationWithStatus(IDVerificationStatus::FAILED);
        $this->expectCardToBeCreatedForShop($shop);

        $this->storer->store($id_verification);
    }

    /**
     * @test
     */
    public function addresses_failure_card_on_verified_verification()
    {
        $shop = $this->givenShopExists();
        $id_verification = $this->makeVerificationWithStatus(IDVerificationStatus::VERIFIED);
        $this->expectCardToBeAddressedForShop($shop);

        $this->storer->store($id_verification);
    }

    private function makeVerificationWithStatus(string $status): IDVerification
    {
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

    private function expectVerificationToBeStored(IDVerification $verification)
    {
        $verification->expects($this->once())
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

    private function expectCardToBeAddressedForShop(Shop $shop)
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
