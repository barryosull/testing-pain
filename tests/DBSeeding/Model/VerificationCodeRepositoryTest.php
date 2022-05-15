<?php

namespace Barryosull\TestingPainTests\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\Message\Message;
use Barryosull\TestingPain\DBSeeding\Message\MessageFactory;
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

    /** @var MessageFactory */
    private $message_factory;

    /** @var VerificationCodeRepository */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->account_repository = $this->createMock(AccountRepository::class);
        $this->message_factory = $this->createMock(MessageFactory::class);

        $this->repository = new VerificationCodeRepository($this->account_repository, $this->message_factory);
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
        $id_verification = $this->makeVerificationWithStatus(VerificationStatus::FAILED);
        $this->expectMessageToBeCreatedForAccount($account);

        $this->repository->store($id_verification);
    }

    public function test_clears_failure_message_on_verified_verification()
    {
        $account = $this->givenAccountExists();
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
        $account->account_id = self::ACCOUNT_ID;
        $this->account_repository->method('find')
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

    private function expectMessageToBeCreatedForAccount(Account $account)
    {
        $message = $this->createMock(Message::class);
        $this->message_factory->method('makeVerificationFailedMessage')
            ->with($account->account_id)
            ->willReturn($message);

        $message->expects($this->once())
            ->method('createForAccount')
            ->with($account);
    }

    private function expectMessageToBeClearedForAccount(Account $account)
    {
        $message = $this->createMock(Message::class);
        $this->message_factory->method('makeVerificationFailedMessage')
            ->with($account->account_id)
            ->willReturn($message);

        $message->expects($this->once())
            ->method('clear')
            ->with($account);
    }
}
