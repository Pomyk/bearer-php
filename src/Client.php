<?php

namespace Bearer;

class Client
{

    static $VERSION = "2.0.1";

    protected $secretKey;
    protected $host = 'https://proxy.bearer.sh';
    protected $httpClientSettings;

    static public $defaultOptions = [
        'maxNetworkRetries' => 0,
        'maxNetworkRetryDelay' => 2,
        'initialNetworkRetryDelay' => 0.5,
    ];

    protected $options = [];

    public function __construct($secretKey, $httpClientSettings = [CURLOPT_TIMEOUT => 5, CURLOPT_CONNECTTIMEOUT => 5])
    {
        $this->setSecretKey($secretKey);
        $this->setHttpClientSettings($httpClientSettings);
        $this->setOptions(self::$defaultOptions);
        return $this;
    }

    public function setSecretKey($apiKey)
    {
        $this->secretKey = $apiKey;
        return $this;
    }

    public function setApiKey($apiKey)
    {
        trigger_error('Please use Bearer\Client::setSecretKey. Method ' . __METHOD__ . ' is deprecated', E_USER_DEPRECATED);
        $this->secretKey = $apiKey;
        return $this;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    protected function setOptions(array $options) {
        $this->options = $options;
    }

    public function setHttpClientSettings($httpClientSettings)
    {
        $this->httpClientSettings = $httpClientSettings;
        return $this;
    }

    public function setMaxNetworkRetries($maxRetries) {
        $this->options['maxNetworkRetries'] = $maxRetries;
        return $this;
    }

    public function setMaxNetworkRetryDelay($maxRetryDelay) {
        $this->options['maxNetworkRetryDelay'] = $maxRetryDelay;
        return $this;
    }

    public function setInitialNetworkRetryDelay($initialRetryDelay) {
        $this->options['initialNetworkRetryDelay'] = $initialRetryDelay;
        return $this;
    }

    public function integration($id, $httpClientSettings = [])
    {
        if (is_array($httpClientSettings)) {
            $httpClientSettings = array_replace($this->httpClientSettings, $httpClientSettings);
        } else {
            $httpClientSettings = [];
        }

        return new Integration(
            $id,
            $this->secretKey,
            $this->host,
            $httpClientSettings,
            $this->options
        );
    }
}
