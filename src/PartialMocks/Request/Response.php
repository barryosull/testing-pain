<?php declare(strict_types=1);

namespace Barryosull\TestingPain\PartialMocks\Request;

interface Response
{
    public function adaptResponse(array $response): array;
}