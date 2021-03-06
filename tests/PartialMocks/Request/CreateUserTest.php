<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\PartialMocks\Request;

use Barryosull\TestingPain\PartialMocks\Request\CreateUser;
use DateTime;
use PHPUnit\Framework\TestCase;

class CreateUserTest extends TestCase
{
    public function test_sends_request_and_parses_result()
    {
        $method = 'POST';
        $partial_uri = '/user/';
        $create_user_request = $this->createPartialMock(CreateUser::class, ['getCredentials', 'makeCall']);

        $credentials = ['username' => 'test', 'password' => 'password'];

        $create_user_request->method('getCredentials')
            ->willReturn($credentials);

        $api_data = [
            'name' => 'Test User',
            'dob' => '10/10/1994',
            'email' => 'test@email.com',
            'tshirt_size' => 1,
        ];

        $response = [
            'status' => 200,
            'data' => [
                'entity_id' => 1,
            ]
        ];

        $create_user_request->method('makeCall')
            ->with($method, $partial_uri, $api_data, $credentials)
            ->willReturn($response);

        $dob = new DateTime('1994-10-10');

        $create_user_request->set('name', 'Test User');
        $create_user_request->set('dob', $dob);
        $create_user_request->set('email', 'test@email.com');
        $create_user_request->set('tshirt_size', 's');

        $result = $create_user_request->send();

        $expected_result = ['user_id' => 1];

        $this->assertEquals($expected_result, $result);
    }
}
