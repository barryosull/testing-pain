<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\Legibility;

class FakeSynchronousApiClient
{
    public static function create($test_case): self
    {
        return new self();
    }
}
