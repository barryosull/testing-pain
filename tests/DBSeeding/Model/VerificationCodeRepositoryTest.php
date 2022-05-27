<?php

namespace Barryosull\TestingPainTests\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\Model\Message;
use Barryosull\TestingPain\DBSeeding\Model\MessageRepository;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCode;
use Barryosull\TestingPain\DBSeeding\Model\VerificationStatus;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCodeRepository;
use Barryosull\TestingPain\DBSeeding\Model\Account;
use Barryosull\TestingPain\DBSeeding\Model\AccountRepository;
use PHPUnit\Framework\TestCase;

class VerificationCodeRepositoryTest extends TestCase
{
    const ACCOUNT_ID = 1;

    /** @var AccountRepository */
    private $account_repository;

    /** @var MessageRepository */
    private $message_repository;

    /** @var VerificationCodeRepository */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->account_repository = $this->createMock(AccountRepository::class);
        $this->message_repository = $this->createMock(MessageRepository::class);

        $this->repository = new VerificationCodeRepository($this->account_repository, $this->message_repository);
    }


    //*********************************
    // Testcases
    //*********************************

    public function test_stores_id_verification()
    {
        $this->givenAccountExists();
        $id_verification = $this->makeVerificationWithStatus(VerificationStatus::VERIFIED);
        $this->expectVerificationToBeStored($id_verification);

        $this->repository->store($id_verification);
    }

    public function test_creates_failure_message_on_failed_verification()
    {
        $account = $this->givenAccountExists();
        $this->givenMessageExists($account);
        $id_verification = $this->makeVerificationWithStatus(VerificationStatus::FAILED);
        $this->expectMessageToBeCreatedForAccount($account);

        $this->repository->store($id_verification);
    }

    public function test_clears_failure_message_on_verified_verification()
    {
        $account = $this->givenAccountExists();
        $this->givenMessageExists($account);
        $id_verification = $this->makeVerificationWithStatus(VerificationStatus::VERIFIED);
        $this->expectMessageToBeClearedForAccount($account);

        $this->repository->store($id_verification);
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

    private function givenAccountExists(): Account
    {
        $account = $this->createMock(Account::class);
        $account->id = self::ACCOUNT_ID;
        $this->account_repository->method('find')
            ->willReturn($account);
        return $account;
    }

    private function givenMessageExists(Account $account)
    {
        $message = new Message($account->id, Message::VERIFICATION_FAILED_TYPE_ID);
        $this->message_repository->method('findByType')
            ->willReturn($message);
    }


    //*********************************
    // Expectations (side effects)
    //*********************************

    private function expectVerificationToBeStored(VerificationCode $verification)
    {
        $verification->expects($this->once())
            ->method('store');
    }

    private function expectMessageToBeCreatedForAccount(Account $account)
    {
        $this->message_repository->expects($this->once())
            ->method('store');
    }

    private function expectMessageToBeClearedForAccount(Account $account)
    {
        $this->message_repository->expects($this->once())
            ->method('store');
    }
}
