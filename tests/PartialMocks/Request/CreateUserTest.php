<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\PartialMocks\Request;

use Barryosull\TestingPain\PartialMocks\Request\CreateUser;
use Barryosull\TestingPain\PartialMocks\Request\HttpClient;
use Barryosull\TestingPain\PartialMocks\Request\Requester;
use DateTime;
use PHPUnit\Framework\TestCase;

class CreateUserTest extends TestCase
{
    /**
     * @test
     */
    public function adapts_request_for_service()
    {
        $name = 'Test User';
        $dob = new DateTime('1994-10-10');
        $email = 'test@email.com';
        $tshirt_size = 's';

        $expected_request = $this->makeExpectedServiceRequest($name, $email);

        $request = new CreateUser($name, $dob, $email, $tshirt_size);

        $adapted_request = $request->makeServiceRequest();

        $this->assertEquals($expected_request, $adapted_request);
    }

    /**
     * @test
     */
    public function adapts_response_from_service()
    {
        $user_id = 1;

        $request = new CreateUser('', new DateTime(), '', '');

        $service_response = $this->makeServiceResponse($user_id);

        $response = $request->adaptResponse($service_response);

        $expected_response = $this->makeExpectedResponse($user_id);

        $this->assertEquals($expected_response, $response);
    }

    private function makeExpectedServiceRequest(string $name, string $email): array
    {
        $expected_dob = '10/10/1994';
        $expected_tshirt_size = 1;
        return [
            'name' => $name,
            'dob' => $expected_dob,
            'email' => $email,
            'tshirt_size' => $expected_tshirt_size,
        ];
    }

    private function makeServiceResponse(int $user_id): array
    {
        return [
            'status' => 200,
            'data' => [
                'entity_id' => $user_id,
            ]
        ];
    }

    private function makeExpectedResponse(int $user_id): array
    {
        return ['user_id' => $user_id];
    }
}
