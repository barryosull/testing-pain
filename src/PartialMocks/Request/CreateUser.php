<?php declare(strict_types=1);

namespace Barryosull\TestingPain\PartialMocks\Request;

use DateTime;

class CreateUser extends AbstractRequest
{
    protected $method = 'POST';

    protected function formatDob(DateTime $dob): string
    {
        return  $dob->format('d/m/Y');
    }

    protected function formatResponse(array $response): array
    {
        return $response['data']['user_id'];
    }
}