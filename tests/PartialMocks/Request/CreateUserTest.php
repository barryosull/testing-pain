<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\PartialMocks\Request;

use Barryosull\TestingPain\PartialMocks\Request\CreateUser;
use Barryosull\TestingPain\PartialMocks\Request\HttpClient;
use Barryosull\TestingPain\PartialMocks\Request\Requester;
use DateTime;
use PHPUnit\Framework\TestCase;

class CreateUserTest extends TestCase
{
    private $http_client;
    private $requester;

    public function setUp(): void
    {
        parent::setUp();

        $this->http_client = $this->createMock(HttpClient::class);

        $this->requester = new Requester($this->http_client);
    }

    /**
     * @test
     */
    public function sends_request_and_parses_result()
    {
        $name = 'Test User';
        $dob = new DateTime('1994-10-10');
        $email = 'test@email.com';
        $tshirt_size = 's';

        $service_request = $this->makeExpectedServiceRequest($name, $email);

        $user_id = 1;
        $service_response = $this->makeServiceResponse($user_id);

        $this->givenRequestGivesResponse($service_request, $service_response);

        $result = $this->whenRequestIsSent($name, $dob, $email, $tshirt_size);

        $expected_result = $this->makeExpectedResponse($user_id);
        $this->assertEquals($expected_result, $result);
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

    private function givenRequestGivesResponse(array $expected_api_data, array $response): void
    {
        $credentials = ['username' => 'test', 'password' => 'password'];
        $this->http_client->method('getCredentials')
            ->willReturn($credentials);

        $method = 'POST';
        $partial_uri = '/user/';
        $this->http_client->method('makeApiCall')
            ->with($method, $partial_uri, $expected_api_data, $credentials)
            ->willReturn($response);
    }

    private function whenRequestIsSent(string $name, DateTime $dob, string $email, string $tshirt_size): array
    {
        $request = new CreateUser($name, $dob, $email, $tshirt_size);
        return $this->requester->makeRequest($request);
    }
}
