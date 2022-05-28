<?php declare(strict_types=1);

namespace Barryosull\TestingPain\PartialMocks\Request;

use Barryosull\TestingPain\PartialMocks\Config;

abstract class AbstractRequest
{
    private string $base_url;
    protected array $request_data = [];
    protected string $method;

    public function __construct()
    {
        $this->base_url = $this->getUrl();
    }

    protected function getUrl(): string
    {
        return Config::get('tshirt_heaven.url');
    }

    public function send(): array
    {
        $this->formatRequest();
        $response = $this->makeCall($this->method, $this->partial_uri, $this->request_data, $this->getCredentials());
        return $this->formatResponse($response);
    }

    public function set(string $key, $value)
    {
        $this->request_data[$key] = $value;
    }

    protected function getCredentials()
    {
        return [
            'username' => Config::get('tshirt_heaven.username'),
            'password' => Config::get('tshirt_heaven.password'),
        ];
    }

    protected function makeCall(string $method, string $partial_uri, array $request, array $credentials): array
    {
        $connection = curl_init($this->base_url . $partial_uri);
        curl_setopt($connection, CURLOPT_USERPWD, $credentials['username'] . ":" . $credentials['password']);
        if ($method === 'POST') {
            curl_setopt($connection, CURLOPT_POSTFIELDS, $request);
        }
        if ($method === 'PUT') {
            curl_setopt($connection, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($connection, CURLOPT_POSTFIELDS,http_build_query($request));
        }
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        $api_response = curl_exec($connection);
        curl_close($connection);

        return json_decode($api_response, true);
    }

    abstract protected function formatRequest(): void;

    abstract protected function formatResponse(array $response): array;
}
