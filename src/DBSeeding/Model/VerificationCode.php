<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

/**
 * @property int $account_id
 * @property string $verification_status
 */
class VerificationCode extends ActiveRecordBaseModel
{
    public $account_id;
    public $verification_status;
}
