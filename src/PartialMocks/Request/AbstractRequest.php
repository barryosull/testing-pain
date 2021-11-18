<?php declare(strict_types=1);

namespace Barryosull\TestingPain\PartialMocks\Request;

use Barryosull\TestingPain\PartialMocks\Config;

abstract class AbstractRequest
{
    private $base_url;
    private $data;

    protected $method;

    public function __construct()
    {
        $this->base_url = $this->getUrl();
        $this->data = [];
    }

    protected function getUrl(): string
    {
        return Config::get('tshirt_heaven.url');
    }

    public function send(): array
    {
        $response = $this->makeCall($this->method, $this->partial_uri, $this->data, $this->getCredentials());
        return $this->formatResponse($response);
    }

    public function set(string $key, $value)
    {
        $format_method = 'format' . ucfirst($key);
        if (method_exists($this, $format_method)) {
            $value = $this->$format_method($value);
        }
        $this->data[$key] = $value;
    }

    protected function getCredentials()
    {
        return [
            'username' => Config::get('tshirt_heaven.username'),
            'password' => Config::get('tshirt_heaven.password'),
        ];
    }

    protected function makeCall(string $method, string $partial_uri, array $data, array $credentials): array
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

    abstract protected function formatResponse(array $response): array;
}
