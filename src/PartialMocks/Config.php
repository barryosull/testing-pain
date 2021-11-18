<?php declare(strict_types=1);

namespace Barryosull\TestingPain\PartialMocks;

class Config
{
    public static function get(string $key):? string
    {
        // Access DB to get config value
        return null;
    }
}
