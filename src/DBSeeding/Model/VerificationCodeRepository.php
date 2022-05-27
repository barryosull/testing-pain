<?php

namespace Barryosull\TestingPain\DBSeeding\Model;

class VerificationCodeRepository
{
    /** @var AccountRepository */
    private $account_repository;

    /** @var MessageRepository */
    private $message_repository;

    public function __construct(AccountRepository $account_repository, MessageRepository $message_repository)
    {
        $this->account_repository = $account_repository;
        $this->message_repository = $message_repository;
    }

    public function store(VerificationCode $verification_code): void
    {
        $verification_code->store();

        $this->updateUi($verification_code);
    }

    private function updateUi(VerificationCode $verification_code): void
    {
        $account = $this->account_repository->find($verification_code->account_id);

        $message_type_id = Message::VERIFICATION_FAILED_TYPE_ID;

        $verification_failed_message = $this->message_repository->findByType($account->id, $message_type_id);

        if ($verification_failed_message === null) {
            $verification_failed_message = new Message($account->id, $message_type_id);
        }

        if ($verification_code->verification_status === VerificationStatus::FAILED) {
            $verification_failed_message->display();
        }
        if ($verification_code->verification_status === VerificationStatus::VERIFIED) {
            $verification_failed_message->clear();
        }
        $this->message_repository->store($verification_failed_message);
    }

    public function find(int $account_id): ?VerificationCode
    {
        // details omitted
        return null;
    }
}
