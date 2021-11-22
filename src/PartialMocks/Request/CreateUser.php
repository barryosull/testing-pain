<?php declare(strict_types=1);

namespace Barryosull\TestingPain\PartialMocks\Request;

use DateTime;

class CreateUser implements Request
{
    private $name;
    private $dob;
    private $email;
    private $tshirt_size;

    public function __construct(string $name, DateTime $dob, string $email, string $tshirt_size)
    {
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
        return 'POST';
    }

    public function makeServiceRequest(): array
    {
        return [
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

    public function adaptResponse(array $response): array
    {
        return [
            'user_id' => $response['data']['entity_id']
        ];
    }
}
