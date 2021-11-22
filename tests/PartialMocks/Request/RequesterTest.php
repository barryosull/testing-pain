<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\PartialMocks\Request;

use Barryosull\TestingPain\PartialMocks\Request\HttpClient;
use Barryosull\TestingPain\PartialMocks\Request\Request;
use Barryosull\TestingPain\PartialMocks\Request\Requester;
use PHPUnit\Framework\TestCase;

class RequesterTest extends TestCase
{
    private $http_client;
    private $requester;

    public function setUp(): void
    {
        parent::setUp();

        $this->http_client = $this->createMock(HttpClient::class);

        $this->requester = new Requester($this->http_client);
    }

    public function test_sends_request_and_parses_result()
    {
        $request = $this->createMock(Request::class);

        $service_response = ['dummy_type' => 'service_response'];
        $response = ['dummy_type' => 'response'];

        $this->givenRequestGivesResponse($request, $service_response);
        $this->givenResponseIsAdapted($request, $service_response, $response);

        $actual_response = $this->requester->makeRequest($request);

        $this->assertEquals($response, $actual_response);
    }

    private function givenRequestGivesResponse(Request $request, array $service_response): void
    {
        $this->http_client->method('makeApiCall')
            ->with($request)
            ->willReturn($service_response);
    }

    private function givenResponseIsAdapted(Request $request, array $service_response, array $response): void
    {
        $request->method('adaptResponse')
            ->with($service_response)
            ->willReturn($response);
    }
}
