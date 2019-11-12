<?php
namespace Bearer\Tests;

use Bearer\RetryStrategy;

class RequestStrategyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider sleepTimeData
     */
    public function testSleepTime($retries, $minTime, $maxTime) {
        $testConfig = require '_config.php';

        $retryStrategy = new RetryStrategy($testConfig);

        for ($i = 0; $i < 100; $i++) {
            $sleepTime = $retryStrategy->sleepTime($retries);
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
        $testConfig = require '_config.php';
        $testConfig['maxNetworkRetries'] = 2;
        $retryStrategy = new RetryStrategy($testConfig);

        $shouldRetry = $retryStrategy->shouldRetry($error, $status, $numRetries);
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
