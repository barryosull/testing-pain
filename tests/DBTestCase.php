<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests;

use Barryosull\TestingPain\DBSeeding\Model\Account;
use Barryosull\TestingPain\DBSeeding\Model\ActiveRecordBaseModel;
use Barryosull\TestingPain\DBSeeding\Model\MessageType;
use Barryosull\TestingPain\DBSeeding\Model\VerificationCode;
use PHPUnit\Framework\TestCase;

abstract class DBTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->pretendDBIsCreated();

        ActiveRecordBaseModel::clearStore();

        $seed_data = $this->getDBSeedData();
        foreach ($seed_data as $table => $rows) {
            foreach ($rows as $row) {
                $this->seedTableRow($table, $row);
            }
        }
    }

    private static bool $is_db_created = false;

    private function pretendDBIsCreated()
    {
        if (self::$is_db_created) {
            return;
        }
        fwrite(STDERR, "Creating database\n");
        for ($i = 0; $i < 5; $i++) {
            usleep(500000);
            fwrite(STDERR, ". ");
        }
        fwrite(STDERR, "\nDatabase created\n\nRunning tests:\n");
        self::$is_db_created = true;
    }

    abstract protected function getDBSeedData(): array;

    private function seedTableRow(string $table, array $row) {
        if ($table === 'accounts') {
            $account = new Account();
            $account->account_id = $row['account_id'];
            $account->store();
        }
        if ($table === 'verification_codes') {
            $verification_code = new VerificationCode();
            $verification_code->account_id = $row['account_id'];
            $verification_code->verification_code_id = $row['verification_code_id'];
            $verification_code->store();
        }
        if ($table === 'message_types') {
            $message_type = new MessageType();
            $message_type->message_type_id = $row['message_type_id'];
            $message_type->store();
        }
    }
}
