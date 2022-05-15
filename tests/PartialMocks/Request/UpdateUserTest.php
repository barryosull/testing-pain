<?php declare(strict_types=1);

namespace Barryosull\TestingPainTests\PartialMocks\Request;

use Barryosull\TestingPain\PartialMocks\Request\UpdateUser;
use DateTime;
use PHPUnit\Framework\TestCase;

class UpdateUserTest extends TestCase
{
    const USER_ID = 1;
    const EMAIL = 'test@email.com';
    const NAME = 'Test User2';

    /** @var UpdateUser */
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = $this->makeRequestWithMockedApi();
    }

    public function test_sends_request()
    {
        $this->givenRequestIsPrepared();

        $this->expectFormattedApiRequestIsSent();

        $this->request->send();
    }

    public function test_parses_result()
    {
        $this->givenRequestIsPrepared();
        $this->givenApiCallReturnsResponse();

        $result = $this->request->send();

        $expected_result = $this->makeExpectedResponse();
        $this->assertEquals($expected_result, $result);
    }

    private function makeRequestWithMockedApi(): UpdateUser
    {
        return $this->createPartialMock(UpdateUser::class, ['getCredentials', 'makeCall']);
    }

    private function makeExpectedApiRequest(): array
    {
        return [
            'user_id' => self::USER_ID,
            'name' => self::NAME,
            'dob' => '10/10/1993',
            'email' => self::EMAIL,
            'tshirt_size' => 2
        ];
    }

    private function makeExpectedApiResponse(): array
    {
        return [
            'status' => 200,
            'data' => [
                'entity_id' => self::USER_ID,
            ]
        ];
    }

    private function makeExpectedResponse(): array
    {
        return ['user_id' => self::USER_ID];
    }

    private function givenRequestIsPrepared(): void
    {
        $dob = new DateTime('1993-10-10');

        $this->request->set('user_id', self::USER_ID);
        $this->request->set('name', self::NAME);
        $this->request->set('dob', $dob);
        $this->request->set('email', self::NAME);
        $this->request->set('tshirt_size', 'm');
    }

    private function givenApiCallReturnsResponse(): void
    {
        $response = $this->makeExpectedApiResponse();

        $this->request->method('makeCall')
            ->willReturn($response);
    }

    private function givenValidCredentials(): array
    {
        $credentials = ['username' => 'test', 'password' => 'password'];

        $this->request->method('getCredentials')
            ->willReturn($credentials);

        return $credentials;
    }

    private function expectFormattedApiRequestIsSent()
    {
        $credentials = $this->givenValidCredentials();

        $method = 'PUT';
        $partial_uri = '/user/';

        $api_data = $this->makeExpectedApiRequest();

        $response = $this->makeExpectedApiResponse();

        $this->request->expects($this->once())
            ->method('makeCall')
            ->with($method, $partial_uri, $api_data, $credentials)
            ->willReturn($response);
    }
}
