<?php

namespace Bearer;

class Client
{
    static $VERSION = "3.0.0";

    protected $secretKey;
    protected $host = 'https://proxy.bearer.sh';

    protected $options = [];

    public function __construct($secretKey, $options = [])
    {
        $this->setSecretKey($secretKey);
        $this->options = array_merge([
            'timeout' => 5,
            'connectTimeout' => 5,
            'httpClientConfig' => [],
            'maxNetworkRetries' => 0,
            'maxNetworkRetryDelay' => 2,
            'initialNetworkRetryDelay' => 0.5,
            'requestHandler' => null,
            'logger' => null,
        ], $options);
        return $this;
    }

    public function setSecretKey($apiKey)
    {
        $this->secretKey = $apiKey;
        return $this;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    public function setHttpClientConfig($httpClientConfig)
    {
        $this->options['httpClientConfig'] = $httpClientConfig;
        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->options['timeout'] = $timeout;
        return $this;
    }

    public function setConnectTimeout($connectTimeout)
    {
        $this->options['connectTimeout'] = $connectTimeout;
        return $this;
    }

    public function setMaxNetworkRetries($maxRetries)
    {
        $this->options['maxNetworkRetries'] = $maxRetries;
        return $this;
    }

    public function setMaxNetworkRetryDelay($maxRetryDelay)
    {
        $this->options['maxNetworkRetryDelay'] = $maxRetryDelay;
        return $this;
    }

    public function setInitialNetworkRetryDelay($initialRetryDelay)
    {
        $this->options['initialNetworkRetryDelay'] = $initialRetryDelay;
        return $this;
    }

    public function setRequestHandler($requestHandler)
    {
        $this->options['requestHandler'] = $requestHandler;
        return $this;
    }

    public function integration($id, $options = [])
    {
        if (is_array($options)) {
            $options = array_replace($this->options, $options);
        } else {
            $options = [];
        }

        return new Integration(
            $id,
            $this->secretKey,
            $this->host,
            $this->options
        );
    }
}
