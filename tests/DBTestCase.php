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
                $model = $this->seedTableRow($table, $row);
                $model->store();
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

    private function seedTableRow(string $table, array $row): ActiveRecordBaseModel {
        if ($table === 'accounts') {
            return Account::makeFromDbRow($row);
        }
        if ($table === 'verification_codes') {
            return VerificationCode::makeFromDbRow($row);
        }
        if ($table === 'message_types') {
            return MessageType::makeFromDbRow($row);
        }
        throw new \Exception("Cannot create model for table '{$table}'");
    }
}
