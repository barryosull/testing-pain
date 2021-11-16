<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\AdvisoryCard\IDVerificationFailed;

/**
 * @property int $shop_id
 * @property string $verification_status
 */
class IDVerification extends ActiveRecordBaseModel {

    public function recordStored($dirtyData = null) {
        parent::recordStored($dirtyData);

        $shop = (new ShopFinder)->find($this->shop_id);

        $verification_failure_card = new IDVerificationFailed();

        if ($this->verification_status === IDVerificationStatus::FAILED) {
            $verification_failure_card->createForShop($shop);
        } else {
            $verification_failure_card->markAsAddressed($shop);
        }
    }
}
