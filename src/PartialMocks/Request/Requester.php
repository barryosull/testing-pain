<?php declare(strict_types=1);

namespace Barryosull\TestingPain\PartialMocks\Request;

class Requester
{
    public function __construct(private HttpClient $http_client) {}

    public function makeRequest(Request $request): array
    {
        $response = $this->http_client->makeApiCall($request);
        return $request->adaptResponse($response);
    }
}