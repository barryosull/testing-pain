<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\PartialMocks\Request;

use Barryosull\TestingPain\PartialMocks\Request\CreateUser;
use Barryosull\TestingPain\PartialMocks\Request\HttpClient;
use Barryosull\TestingPain\PartialMocks\Request\Requester;
use DateTime;
use PHPUnit\Framework\TestCase;

class CreateUserTest extends TestCase
{
    private $name = 'Test User';
    private $email = 'test@email.com';
    private $user_id = 1;

    /**
     * @test
     */
    public function makes_request_for_service()
    {
        $request = $this->makeRequest();

        $service_request = $request->makeServiceRequest();

        $expected_request = $this->makeExpectedServiceRequest();

        $this->assertEquals($expected_request, $service_request);
    }

    /**
     * @test
     */
    public function adapts_response_from_service()
    {
        $request = $this->makeRequest();

        $service_response = $this->makeServiceResponse();

        $response = $request->adaptResponse($service_response);

        $expected_response = $this->makeExpectedResponse();

        $this->assertEquals($expected_response, $response);
    }

    private function makeRequest(): CreateUser {
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
