<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\PartialMocks\Request;

use Barryosull\TestingPain\PartialMocks\Request\UpdateUser;
use DateTime;
use PHPUnit\Framework\TestCase;

class CreateUserTest extends TestCase
{
    /**
     * @test
     */
    public function sends_request_and_parses_result()
    {
        $method = 'POST';
        $partial_uri = '/user/';
        $update_user_request = $this->createPartialMock(UpdateUser::class, ['getCredentials', 'makeApiCall']);

        $credentials = ['username' => 'test', 'password' => 'password'];

        $update_user_request->method('getCredentials')
            ->willReturn($credentials);

        $api_data = [
            'user_id' => 1,
            'name' => 'Test User2',
            'dob' => '10/10/1993',
            'email' => 'test@email2.com',
            'tshirt_size' => 'm',
        ];

        $response = [
            'status' => 200,
            'data' => [
                'user_id' => 1,
            ]
        ];

        $update_user_request->method('makeApiCall')
            ->with($method, $partial_uri, $api_data, $credentials)
            ->willReturn($response);

        $dob = new DateTime('1993-10-10');

        $update_user_request->set('name', 'Test User2');
        $update_user_request->set('dob', $dob);
        $update_user_request->set('email', 'test@email2.com');
        $update_user_request->set('tshirt_size', 's');

        $result = $update_user_request->send();

        $expected_result = ['user_id' => 1];

        $this->assertEquals($expected_result, $result);
    }
}
