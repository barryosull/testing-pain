<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\DBSeeding\AdvisoryCard;

use Barryosull\TestingPain\DBSeeding\AdvisoryCard\CardFactory;
use Barryosull\TestingPain\DBSeeding\AdvisoryCard\IDVerificationFailedClosed;
use Barryosull\TestingPain\DBSeeding\AdvisoryCard\IDVerificationFailedWarning;
use PHPUnit\Framework\TestCase;

class CardFactoryTest extends TestCase
{
    /**
     * @dataProvider provideShopsToCards
     */
    public function test_makes_card(int $shop_id, string $expected_class)
    {
        $factory = new CardFactory();
        $card = $factory->makeVerificationFailedCard($shop_id);
        $this->assertInstanceOf($expected_class, $card);
    }

    public function provideShopsToCards(): array {
        return [
            'odd shop id' => [
                1,
                IDVerificationFailedWarning::class
            ],
            'even shop id' => [
                2,
                IDVerificationFailedClosed::class
            ]
        ];
    }
}
