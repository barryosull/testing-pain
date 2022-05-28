<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

class VerificationCode extends ActiveRecordBaseModel
{
    public function __construct(
        public int $verification_code_id,
        public int $account_id,
        public string $code,
        public string $verification_status = VerificationCodeStatus::UNCHECKED,
        public ?int $verification_last_checked_at = null,
    ){}

    protected function recordStored($dirtyData = null): void {
        parent::recordStored($dirtyData);

        $message_type_id = Message::VERIFICATION_FAILED_TYPE_ID;

        $verification_failure_message = Message::findByType($this->account_id, $message_type_id);
        if ($verification_failure_message === null) {
            $verification_failure_message = new Message($this->account_id, $message_type_id);
        }

        if ($this->verification_status === VerificationCodeStatus::FAILED) {
            $verification_failure_message->display();
        } else {
            $verification_failure_message->clear();
        }
        $verification_failure_message->store();
    }

    public static function find(int $verification_code_id): ?VerificationCode
    {
        return self::findByPrimary($verification_code_id);
    }

    public function getPrimaryId(): int
    {
        return $this->verification_code_id;
    }
}
