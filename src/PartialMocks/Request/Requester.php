<?php declare(strict_types=1);

namespace Barryosull\TestingPain\PartialMocks\Request;

class Requester
{
    private $http_client;

    public function __construct(HttpClient $http_client)
    {
        $this->http_client = $http_client;
    }

    public function makeRequest(Request $request): array
    {
        $credentials = $this->http_client->getCredentials();
        $response = $this->http_client->makeApiCall(
            $request->httpMethod(),
            $request->partialUri(),
            $request->makeServiceRequest(),
            $credentials
        );
        return $request->adaptResponse($response);
    }
}