<?php
namespace Bearer\Tests;
use Bearer;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    private $testConfig;
    private $testClient;
    private $testIntegration;

    public function setUp()
    {
        $this->testConfig = require '_config.php';

        $reflector = new \ReflectionClass('\Bearer\Request');
        $this->shouldRetryMethod = $reflector->getMethod('shouldRetry');
        $this->shouldRetryMethod->setAccessible(true);
        $this->sleepTimeMethod = $reflector->getMethod('sleepTime');
        $this->sleepTimeMethod->setAccessible(true);
    }

    public function testMakesGetRequest() {
        $testConfig = $this->testConfig;
        $testConfig['baseUrl'] = 'https://example.org';

        $this->testClient = new Bearer\Request('GET', '/', [], $testConfig);

        $this->assertTrue(!is_null($this->testClient->getResponse()));
    }

    /**
     * @dataProvider sleepTimeData
     */
    public function testSleepTime($retries, $minTime, $maxTime) {
        $requestMock = $this->getMockBuilder('\Bearer\Request')
             ->disableOriginalConstructor()
             ->getMock();

        for ($i = 0; $i < 100; $i++) {
            $sleepTime = $this->sleepTimeMethod->invoke($requestMock, $retries, $this->testConfig['options']);
            $this->assertGreaterThanOrEqual($minTime, $sleepTime, 'Sleep time to low');
            $this->assertLessThanOrEqual($maxTime, $sleepTime, 'Sleep time to high');
        }
    }

    public function sleepTimeData() {
        return [
            [1, 0.25, 0.5],
            [2, 0.5, 1.0],
            [3, 1.0, 2.0],
            [5, 1.0, 2.0],
        ];
    }

    /**
     * @dataProvider shouldRetryData
     */
    public function testShouldRetry($error, $status, $numRetries, $expected) {
        $requestMock = $this->getMockBuilder('\Bearer\Request')
             ->disableOriginalConstructor()
             ->getMock();

        $shouldRetry = $this->shouldRetryMethod->invoke($requestMock, $error, $status, $numRetries, ['maxNetworkRetries' => 2]);
        $this->assertEquals($expected, $shouldRetry, 'Wrong result from shouldRetry');
    }

    public function shouldRetryData() {
        return [
            [CURLE_OK, 200, 0, false],
            [CURLE_OK, 200, 1, false],
            [CURLE_OK, 404, 0, false],
            [CURLE_OK, 500, 1, true],
            [CURLE_OK, 500, 2, false],
            [CURLE_OPERATION_TIMEOUTED, 0, 1, true],
            [CURLE_OPERATION_TIMEOUTED, 0, 3, false],
            [CURLE_COULDNT_RESOLVE_HOST, 0, 1, false],
        ];
    }
}
