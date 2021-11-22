<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\Legibility\Request;

class FakeSynchronousApiClient
{
    public static function create($test_case): self
    {
        return new self();
    }
}
