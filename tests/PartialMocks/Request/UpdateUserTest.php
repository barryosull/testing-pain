<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\PartialMocks\Request;

use Barryosull\TestingPain\PartialMocks\Request\UpdateUser;
use DateTime;
use PHPUnit\Framework\TestCase;

class UpdateUserTest extends TestCase
{
    private const NAME = 'Test User';
    private const EMAIL = 'test@email.com';
    private const USER_ID = 1;

    private UpdateUser $request;

    public function setUp(): void
    {
        parent::setUp();
        $this->request = self::makeRequest();
    }

    public function test_makes_request_for_service()
    {
        $expected_request = self::makeExpectedServiceRequest();

        $service_request = $this->request->makeServiceRequest();

        $this->assertEquals($expected_request, $service_request);
    }

    public function test_adapts_response_from_service()
    {
        $response = $this->request->adaptResponse(self::makeServiceResponse());

        $expected_response = self::makeExpectedResponse();

        $this->assertEquals($expected_response, $response);
    }

    private static function makeRequest(): UpdateUser
    {
        $dob = new DateTime('1994-10-10');
        $tshirt_size = 's';
        return new UpdateUser(self::USER_ID, self::NAME, $dob, self::EMAIL, $tshirt_size);
    }

    private static function makeExpectedServiceRequest(): array
    {
        $expected_dob = '10/10/1994';
        $expected_tshirt_size = 1;
        return [
            'entity_id' => 1,
            'name' => self::NAME,
            'dob' => $expected_dob,
            'email' => self::EMAIL,
            'tshirt_size' => $expected_tshirt_size,
        ];
    }

    private static function makeServiceResponse(): array
    {
        return [
            'status' => 200,
            'data' => [
                'entity_id' => self::USER_ID,
            ]
        ];
    }

    private static function makeExpectedResponse(): array
    {
        return ['user_id' => self::USER_ID];
    }
}

