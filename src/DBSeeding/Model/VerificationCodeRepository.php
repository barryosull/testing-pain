<?php

namespace Barryosull\TestingPain\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\Message\MessageFactory;

class VerificationCodeRepository
{
    /** @var AccountRepository */
    private $account_repository;

    /** @var MessageFactory */
    private $message_factory;

    public function __construct(AccountRepository $account_repository, MessageFactory $message_factory)
    {
        $this->account_repository = $account_repository;
        $this->message_factory = $message_factory;
    }

    public function store(VerificationCode $verification_code): void
    {
        $verification_code->store();

        $this->updateUi($verification_code);
    }

    private function updateUi(VerificationCode $id_verification): void
    {
        $account = $this->account_repository->find($id_verification->account_id);

        $message = $this->message_factory->makeVerificationFailedMessage($account->account_id);

        if ($id_verification->verification_status === VerificationStatus::FAILED) {
            $message->create($account);
        } else {
            $message->clear($account);
        }
    }

    public function find(int $account_id): ?VerificationCode
    {
        // details omitted
        return null;
    }
}
