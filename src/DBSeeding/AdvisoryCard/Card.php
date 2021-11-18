<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\AdvisoryCard;

use Barryosull\TestingPain\DBSeeding\Model\Shop;

interface Card
{
    public function createForShop(Shop $shop);

    public function markAsAddressed(Shop $shop);
}