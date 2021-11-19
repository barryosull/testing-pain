<?php declare(strict_types=1);

namespace Barryosull\TestingPain\PartialMocks\Request;

use Barryosull\TestingPain\PartialMocks\Config;

class HttpClient
{
    private $base_url;

    public function __construct(string $base_url)
    {
        $this->base_url = $base_url;
    }

    public function makeApiCall(string $method, string $partial_uri, array $data, array $credentials): array
    {
        $connection = curl_init($this->base_url . $partial_uri);
        curl_setopt($connection, CURLOPT_USERPWD, $credentials['username'] . ":" . $credentials['password']);
        if ($method === 'POST') {
            curl_setopt($connection, CURLOPT_POSTFIELDS, $data);
        }
        if ($method === 'PUT') {
            curl_setopt($connection, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($connection, CURLOPT_POSTFIELDS,http_build_query($data));
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