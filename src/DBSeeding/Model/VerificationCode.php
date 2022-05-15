<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\Message\VerificationFailed;

/**
 * @property int $account_id
 * @property string $verification_status
 */
class VerificationCode extends ActiveRecordBaseModel
{
    public $account_id;
    public $verification_status;

    protected function recordStored($dirtyData = null) {
        parent::recordStored($dirtyData);

        $account = Account::find($this->account_id);

        $message_type_id = Message::VERIFICATION_FAILED_TYPE_ID;

        $verification_failure_message = Message::findByType($account->id, $message_type_id);
        if ($verification_failure_message === null) {
            $verification_failure_message = new Message($account->id, $message_type_id);
        }

        if ($this->verification_status === VerificationCodeStatus::FAILED) {
            $verification_failure_message->display();
        } else {
            $verification_failure_message->clear();
        }
        $verification_failure_message->store();
    }

    public static function find(int $account_id): ?VerificationCode
    {
        // details omitted
        return null;
    }
}
