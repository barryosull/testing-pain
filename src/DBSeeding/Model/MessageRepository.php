<?php

namespace Barryosull\TestingPain\DBSeeding\Model;

class MessageRepository
{
    /**
     * @param int $account_id
     * @param int $type_id
     * @return Message
     */
    public function findByType(int $account_id, int $type_id): Message {
        return new Message($account_id, $type_id);
    }

    public function store(Message $message): void {

    }
}
