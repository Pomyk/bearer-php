<?php

namespace Bearer;

class RetryStrategy
{
    private $config;

    public function __construct(array $config = [])
    {
        $this->config = array_replace([
            'maxNetworkRetries' => 0,
            'maxNetworkRetryDelay' => 2,
            'initialNetworkRetryDelay' => 0.5,
        ], $config);
    }

    public function shouldRetry($error, $httpStatus, $numRetries)
    {
        if (!empty($this->config['maxNetworkRetries'])) {
            $maxRetries = $this->config['maxNetworkRetries'];
            if ($numRetries >= $maxRetries) {
                return false;
            }
        } else {
            return false;
        }
        // retry on timeout or connect error
        if (in_array($error, [CURLE_OPERATION_TIMEOUTED, CURLE_COULDNT_CONNECT])) {
            return true;
        }
        if ($httpStatus >= 500) {
            return true;
        }
        return false;
    }

    public function sleepTime($numRetries)
    {
        $initialNetworkRetryDelay = $this->config['initialNetworkRetryDelay'];
        $maxNetworkRetryDelay = $this->config['maxNetworkRetryDelay'];
        // exponential backoff with a limit of $maxNetworkRetryDelay
        $sleepSeconds = min(
            $initialNetworkRetryDelay * 1.0 * pow(2, $numRetries - 1),
            $maxNetworkRetryDelay
        );
        // Apply some jitter by randomizing the value in the range of
        // ($sleepSeconds / 2) to ($sleepSeconds).
        $sleepSeconds *= 0.5 * (1 + mt_rand()/mt_getrandmax());

        // But never sleep less than the base sleep seconds.
        $sleepSeconds = max($initialNetworkRetryDelay, $sleepSeconds);
        return $sleepSeconds;
    }
}
