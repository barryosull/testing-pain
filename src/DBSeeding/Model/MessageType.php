<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

class MessageType extends ActiveRecordBaseModel
{
    public function __construct(
        public int $message_type_id = Message::VERIFICATION_FAILED_TYPE_ID,
        public? int $next_message_id =  null,
        public int $next_message_delay =  0,
        public string $segment_data_url =  '',
        public string $segment_description =  '',
        public string $title =  'You are not verified',
        public string $description =  'We need to verify you pronto buster',
        public string $action_url =  'http//www.tiredorwired.com/',
        public string $action_text =  'Do the thing',
        public string $category =  'finances',
        public int $priority =  4,
        public string $status =  'published',
        public int $start_date =  0,
        public int $end_date =  0,
        public int $create_date =  1387384309,
        public int $update_date =  1387384309,
        public int $duration =  -1,
        public int $type =  256,
        public int $dismissal_duration =  0,
        public int $duration_type =  1,
        public string $image_url =  '/images/account-tools/dashboard/notifications.svg',
        public string $image_url_2 =  '/images/account-tools/dashboard/notifications.svg',
        public int $zone = 22,
        public int $target_platform = 1,
    ){}

    public function getPrimaryId(): int
    {
        return $this->message_type_id;
    }
}
