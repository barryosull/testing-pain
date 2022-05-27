<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

/**
 * @property int $account_id
 * @property string $verification_status
 */
class VerificationCode extends ActiveRecordBaseModel
{
    /** @var int */
    public $verification_code_id;

    /** @var int */
    public $account_id;

    /** @var string */
    public $verification_status;

    protected function recordStored($dirtyData = null) {
        parent::recordStored($dirtyData);

        $account = Account::find($this->account_id);

        $message_type_id = Message::VERIFICATION_FAILED_TYPE_ID;

        $verification_failure_message = Message::findByType($account->account_id, $message_type_id);
        if ($verification_failure_message === null) {
            $verification_failure_message = new Message($account->account_id, $message_type_id);
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

    public function getPrimaryId()
    {
        return $this->verification_code_id;
    }
}
