<?php declare(strict_types=1);

namespace Barryosull\TestingPain\Legibility\Model;

class Shop
{
    public function getIsBusiness(): bool
    {
        return true;
    }

    public function Address(): Address
    {
        return new Address();
    }
}
