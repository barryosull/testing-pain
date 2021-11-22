<?php declare(strict_types=1);

namespace Barryosull\TestingPain\Legibility\HTTP;

class Input
{
    public function newInputWithData(array $data)
    {
        return new self();
    }
}