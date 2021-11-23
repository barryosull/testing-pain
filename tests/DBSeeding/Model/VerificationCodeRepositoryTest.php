<?php

namespace Barryosull\TestingPainTests\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\AdvisoryCard\Card;
use Barryosull\TestingPain\DBSeeding\AdvisoryCard\CardFactory;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCode;
use Barryosull\TestingPain\DBSeeding\Model\VerificationStatus;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCodeRepository;
use Barryosull\TestingPain\DBSeeding\Model\Account;
use Barryosull\TestingPain\DBSeeding\Model\AccountFinder;
use PHPUnit\Framework\TestCase;

class VerificationCodeRepositoryTest extends TestCase
{
    const ACCOUNT_ID = 1;

    /** @var AccountFinder */
    private $account_finder;
    /** @var CardFactory */
    private $card_factory;

    private $storer;

    public function setUp(): void
    {
        parent::setUp();

        $this->account_finder = $this->createMock(AccountFinder::class);
        $this->card_factory = $this->createMock(CardFactory::class);

        $this->storer = new VerificationCodeRepository($this->account_finder, $this->card_factory);
    }


    //*********************************
    // Testcases
    //*********************************

    public function test_stores_id_verification()
    {
        $this->givenShopExists();
        $id_verification = $this->makeVerificationWithStatus(VerificationStatus::VERIFIED);
        $this->expectVerificationToBeStored($id_verification);

        $this->storer->store($id_verification);
    }

    public function test_creates_failure_card_on_failed_verification()
    {
        $account = $this->givenShopExists();
        $id_verification = $this->makeVerificationWithStatus(VerificationStatus::FAILED);
        $this->expectCardToBeCreatedForShop($account);

        $this->storer->store($id_verification);
    }

    public function test_addresses_failure_card_on_verified_verification()
    {
        $account = $this->givenShopExists();
        $id_verification = $this->makeVerificationWithStatus(VerificationStatus::VERIFIED);
        $this->expectCardToBeAddressedForShop($account);

        $this->storer->store($id_verification);
    }


    //*********************************
    // Factories
    //*********************************

    private function makeVerificationWithStatus(string $status): VerificationCode
    {
        $verification = $this->createMock(VerificationCode::class);
        $verification->account_id = self::ACCOUNT_ID;
        $verification->verification_status = $status;
        return $verification;
    }


    //*********************************
    // Given (fakes)
    //*********************************

    private function givenShopExists(): Account
    {
        $account = $this->createMock(Account::class);
        $account->account_id = self::ACCOUNT_ID;
        $this->account_finder->method('find')
            ->willReturn($account);
        return $account;
    }


    //*********************************
    // Expectations (side effects)
    //*********************************

    private function expectVerificationToBeStored(VerificationCode $verification)
    {
        $verification->expects($this->once())
            ->method('store');
    }

    private function expectCardToBeCreatedForShop(Account $account)
    {
        $card = $this->createMock(Card::class);
        $this->card_factory->method('makeVerificationFailedCard')
            ->with($account->account_id)
            ->willReturn($card);

        $card->expects($this->once())
            ->method('createForAccount')
            ->with($account);
    }

    private function expectCardToBeAddressedForShop(Account $account)
    {
        $card = $this->createMock(Card::class);
        $this->card_factory->method('makeVerificationFailedCard')
            ->with($account->account_id)
            ->willReturn($card);

        $card->expects($this->once())
            ->method('markAsAddressed')
            ->with($account);
    }
}
