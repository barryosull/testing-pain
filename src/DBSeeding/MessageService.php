<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding;

use Barryosull\TestingPain\DBSeeding\Model\Message;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCode\StatusChangedEvent;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCodeStatus;

class MessageService
{
    public function bootEventListeners(): void
    {
        EventListener::listenTo(StatusChangedEvent::class, function(StatusChangedEvent $event) {
            $this->handleStatusChangedEvent($event);
        });
    }

    private function handleStatusChangedEvent(StatusChangedEvent $event) {
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