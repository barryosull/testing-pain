<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\PartialMocks\Request;

use Barryosull\TestingPain\PartialMocks\Request\HttpClient;
use Barryosull\TestingPain\PartialMocks\Request\Request;
use Barryosull\TestingPain\PartialMocks\Request\Requester;
use PHPUnit\Framework\TestCase;

class RequesterTest extends TestCase
{
    /** @var HttpClient */
    private $http_client;

    /** @var Request */
    private $request;

    /** @var Requester */
    private $requester;

    public function setUp(): void
    {
        parent::setUp();
        $this->http_client = $this->createMock(HttpClient::class);
        $this->request = $this->createMock(Request::class);

        $this->requester = new Requester($this->http_client);
    }

    public function test_sends_request_and_parses_result()
    {
        $service_response = ['dummy_type' => 'service_response'];
        $response = ['dummy_type' => 'response'];

        $this->givenRequestGivesResponse($service_response);
        $this->givenResponseIsAdapted($service_response, $response);

        $actual_response = $this->requester->makeRequest($this->request);

        $this->assertEquals($response, $actual_response);
    }

    private function givenRequestGivesResponse(array $service_response): void
    {
        $this->http_client->method('makeApiCall')
            ->with($this->request)
            ->willReturn($service_response);
    }

    private function givenResponseIsAdapted(array $service_response, array $response): void
    {
        $this->request->method('adaptResponse')
            ->with($service_response)
            ->willReturn($response);
    }
}
