<?php declare(strict_types=1);

namespace Barryosull\TestingPain\PartialMocks\Request;

use Barryosull\TestingPain\PartialMocks\Config;

class HttpClient
{
    public function __construct(private string $base_url) {}

    public function makeApiCall(Request $request): array
    {
        $credentials = $this->getCredentials();

        $connection = curl_init($this->base_url . $request->partialUri());
        curl_setopt($connection, CURLOPT_USERPWD, $credentials['username'] . ":" . $credentials['password']);
        if ($request->httpMethod() === 'POST') {
            curl_setopt($connection, CURLOPT_POSTFIELDS, $request->makeServiceRequest());
        }
        if ($request->httpMethod() === 'PUT') {
            curl_setopt($connection, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($connection, CURLOPT_POSTFIELDS,http_build_query($request->makeServiceRequest()));
        }
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        $api_response = curl_exec($connection);
        curl_close($connection);

        return json_decode($api_response, true);
    }

    public function getCredentials(): array
    {
        return [
            'username' => Config::get('tshirt_heaven.username'),
            'password' => Config::get('tshirt_heaven.password'),
        ];
    }
}