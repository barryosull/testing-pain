<?php declare(strict_types=1);

namespace Barryosull\TestingPain\PartialMocks\Request;

interface Request
{
    public function partialUri(): string;

    public function httpMethod(): string;

    public function makeServiceRequest(): array;

    public function adaptResponse(array $response): array;
}