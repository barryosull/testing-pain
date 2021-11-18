<?php declare(strict_types=1);

namespace Barryosull\TestingPain\PartialMocks\Request;

use DateTime;

class UpdateUser extends AbstractRequest
{
    protected $method = 'PUT';
    protected $partial_uri = '/user/';

    protected function formatDob(DateTime $dob): string
    {
        return  $dob->format('d/m/Y');
    }

    protected function formatResponse(array $response): array
    {
        return [
            'user_id' => $response['data']['entity_id']
        ];
    }
}