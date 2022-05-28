<?php declare(strict_types=1);

namespace Barryosull\TestingPain\PartialMocks\Request;

use DateTime;

class UpdateUser extends AbstractRequest
{
    protected string $method = 'PUT';
    protected string $partial_uri = '/user/';

    protected function formatDob(DateTime $dob): string
    {
        return  $dob->format('d/m/Y');
    }

    protected function formatTshirtSize(string $size): int
    {
        if ($size === 's') {
            return 1;
        }
        if ($size === 'm') {
            return 2;
        }
        return 3;
    }

    protected function formatResponse(array $response): array
    {
        return [
            'user_id' => $response['data']['entity_id']
        ];
    }
}