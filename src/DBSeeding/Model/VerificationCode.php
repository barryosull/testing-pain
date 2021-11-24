<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\AdvisoryCard\VerificationFailed;

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

        $account = (new AccountFinder)->find($this->account_id);

        $verification_failure_card = new VerificationFailed();

        if ($this->verification_status === VerificationCodeStatus::FAILED) {
            $verification_failure_card->createForAccount($account);
        } else {
            $verification_failure_card->markAsAddressed($account);
        }
    }
}
