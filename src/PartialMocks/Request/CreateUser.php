<?php declare(strict_types=1);

namespace Barryosull\TestingPain\PartialMocks\Request;

class CreateUser extends AbstractRequest
{
    protected string $method = 'POST';
    protected string $partial_uri = '/user/';

    protected function formatRequest(): void
    {
        $this->request_data['tshirt_size'] = $this->formatTshirtSize($this->request_data['tshirt_size']);
        $this->request_data['dob'] = $this->request_data['dob']->format('d/m/Y');
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