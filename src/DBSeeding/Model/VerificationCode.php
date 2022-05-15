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

        $verification_failure_message = new VerificationFailed();

        if ($this->verification_status === VerificationCodeStatus::FAILED) {
            $verification_failure_message->create($account);
        } else {
            $verification_failure_message->clear($account);
        }
        $verification_failure_message->store();
    }

    public static function find(int $account_id): ?VerificationCode
    {
        // details omitted
        return null;
    }
}
