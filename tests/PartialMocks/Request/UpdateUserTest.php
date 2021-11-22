<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\PartialMocks\Request;

use Barryosull\TestingPain\PartialMocks\Request\UpdateUser;
use DateTime;
use PHPUnit\Framework\TestCase;

class UpdateUserTest extends TestCase
{
    private $name = 'Test User';
    private $email = 'test@email.com';
    private $user_id = 1;

    public function test_makes_request_for_service()
    {
        $request = $this->makeRequest();

        $service_request = $request->makeServiceRequest();

        $expected_request = $this->makeExpectedServiceRequest();

        $this->assertEquals($expected_request, $service_request);
    }

    public function test_adapts_response_from_service()
    {
        $request = $this->makeRequest();

        $service_response = $this->makeServiceResponse();

        $response = $request->adaptResponse($service_response);

        $expected_response = $this->makeExpectedResponse();

        $this->assertEquals($expected_response, $response);
    }

    private function makeRequest(): UpdateUser
    {
        $dob = new DateTime('1994-10-10');
        $tshirt_size = 's';
        return new UpdateUser($this->user_id, $this->name, $dob, $this->email, $tshirt_size);
    }

    private function makeExpectedServiceRequest(): array
    {
        $expected_dob = '10/10/1994';
        $expected_tshirt_size = 1;
        return [
            'entity_id' => 1,
            'name' => $this->name,
            'dob' => $expected_dob,
            'email' => $this->email,
            'tshirt_size' => $expected_tshirt_size,
        ];
    }

    private function makeServiceResponse(): array
    {
        return [
            'status' => 200,
            'data' => [
                'entity_id' => $this->user_id,
            ]
        ];
    }

    private function makeExpectedResponse(): array
    {
        return ['user_id' => $this->user_id];
    }
}

