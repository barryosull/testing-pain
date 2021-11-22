<?php declare(strict_types=1);

namespace Barryosull\TestingPain\PartialMocks\Request;

use DateTime;

class UpdateUser implements Request
{
    private $user_id;
    private $name;
    private $dob;
    private $email;
    private $tshirt_size;

    public function __construct(int $user_id, string $name, DateTime $dob, string $email, string $tshirt_size)
    {
        $this->user_id = $user_id;
        $this->name = $name;
        $this->dob = $dob;
        $this->email = $email;
        $this->tshirt_size = $tshirt_size;
    }

    public function partialUri(): string
    {
        return '/user/';
    }

    public function httpMethod(): string
    {
        return 'PUT';
    }

    public function makeServiceRequest(): array
    {
        return [
            'entity_id' => $this->user_id,
            'name' => $this->name,
            'dob' => $this->formatDob($this->dob),
            'email' => $this->email,
            'tshirt_size' => $this->formatTshirtSize($this->tshirt_size)
        ];
    }

    private function formatDob(DateTime $dob): string
    {
        return  $dob->format('d/m/Y');
    }

    private function formatTshirtSize(string $size): int
    {
        if ($size === 's') {
            return 1;
        }
        if ($size === 'm') {
            return 2;
        }
        return 3;
    }

    public function adaptResponse(array $service_response): array
    {
        return [
            'user_id' => $service_response['data']['entity_id']
        ];
    }
}
