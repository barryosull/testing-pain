<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

/**
 * @property int $shop_id
 * @property string $verification_status
 */
class IDVerification extends ActiveRecordBaseModel {
    public $shop_id;
    public $verification_status;
}
