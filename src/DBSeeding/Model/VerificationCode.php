<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\EventListener;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCode\StatusChangedEvent;

/**
 * @property int $account_id
 * @property string $verification_status
 */
class VerificationCode extends ActiveRecordBaseModel
{
    public int $verification_code_id;
    public int $account_id;
    public string $code;
    public string $verification_status = VerificationCodeStatus::UNCHECKED;
    public ?int $verification_last_checked_at = null;

    protected function recordStored($dirtyData = null)
    {
        parent::recordStored($dirtyData);

        $verification_status = $this->verification_status;
        $account_id = $this->account_id;
        EventListener::handle(new StatusChangedEvent($account_id, $verification_status));
    }

    public static function find(int $verification_code_id): ?VerificationCode
    {
        return self::findByPrimary($verification_code_id);
    }

    public function getPrimaryId()
    {
        return $this->verification_code_id;
    }

    public function handleStatusChangedEvent(StatusChangedEvent $event) {
        if ($event->status === VerificationCodeStatus::UNCHECKED) {
            return;
        }

        $message_type_id = Message::VERIFICATION_FAILED_TYPE_ID;
        $verification_failure_message = Message::findByType($event->account_id, $message_type_id);

        if ($verification_failure_message === null) {
            $verification_failure_message = new Message($event->account_id, $message_type_id);
        }

        if ($event->status === VerificationCodeStatus::FAILED) {
            $verification_failure_message->display();
        }
        if ($event->status === VerificationCodeStatus::VERIFIED) {
            $verification_failure_message->clear();
        }
        $verification_failure_message->store();
    }
}

EventListener::listenTo(StatusChangedEvent::class, function (StatusChangedEvent $event) {
    (new VerificationCode())->handleStatusChangedEvent($event);
});
