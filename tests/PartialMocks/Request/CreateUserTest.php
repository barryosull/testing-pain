<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\PartialMocks\Request;

use Barryosull\TestingPain\PartialMocks\Request\CreateUser;
use DateTime;
use PHPUnit\Framework\TestCase;

class CreateUserTest extends TestCase
{
    private $name = 'Test User';
    private $email = 'test@email.com';
    private $user_id = 1;

    public function test_makes_request_for_service()
    {
        $expected_request = $this->makeExpectedServiceRequest();

        $service_request = $this->whenServiceRequestIsMade();

        $this->assertEquals($expected_request, $service_request);
    }

    public function test_adapts_response_from_service()
    {
        $response = $this->whenResponseIsAdapted();

        $expected_response = $this->makeExpectedResponse();

        $this->assertEquals($expected_response, $response);
    }

    private function whenServiceRequestIsMade(): array
    {
        $request = $this->makeRequest();
        return $request->makeServiceRequest();
    }

    private function whenResponseIsAdapted(): array
    {
        $request = $this->makeRequest();
        $service_response = $this->makeServiceResponse();
        return $request->adaptResponse($service_response);
    }

    private function makeRequest(): CreateUser
    {
        $dob = new DateTime('1994-10-10');
        $tshirt_size = 's';
        return new CreateUser($this->name, $dob, $this->email, $tshirt_size);
    }

    private function makeExpectedServiceRequest(): array
    {
        $expected_dob = '10/10/1994';
        $expected_tshirt_size = 1;
        return [
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
