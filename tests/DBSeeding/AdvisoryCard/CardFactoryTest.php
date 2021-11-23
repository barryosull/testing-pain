<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\DBSeeding\AdvisoryCard;

use Barryosull\TestingPain\DBSeeding\AdvisoryCard\CardFactory;
use Barryosull\TestingPain\DBSeeding\AdvisoryCard\VerificationFailedClosed;
use Barryosull\TestingPain\DBSeeding\AdvisoryCard\VerificationFailedWarning;
use PHPUnit\Framework\TestCase;

class CardFactoryTest extends TestCase
{
    /**
     * @dataProvider provideShopsToCards
     */
    public function test_makes_card(int $account_id, string $expected_class)
    {
        $factory = new CardFactory();
        $card = $factory->makeVerificationFailedCard($account_id);
        $this->assertInstanceOf($expected_class, $card);
    }

    public function provideShopsToCards(): array
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
