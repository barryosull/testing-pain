<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\DBSeeding\Message;

use Barryosull\TestingPain\DBSeeding\Message\MessageFactory;
use Barryosull\TestingPain\DBSeeding\Message\VerificationFailedClosed;
use Barryosull\TestingPain\DBSeeding\Message\VerificationFailedWarning;
use PHPUnit\Framework\TestCase;

class MessageFactoryTest extends TestCase
{
    /**
     * @dataProvider provideAccountsToMessage
     */
    public function test_makes_message(int $account_id, string $expected_class)
    {
        $factory = new MessageFactory();
        $message = $factory->makeVerificationFailedMessage($account_id);
        $this->assertInstanceOf($expected_class, $message);
    }

    public function provideAccountsToMessage(): array
    {
        return [
            'odd account id' => [
                1,
                VerificationFailedWarning::class
            ],
            'even account id' => [
                2,
                VerificationFailedClosed::class
            ]
        ];
    }
}
