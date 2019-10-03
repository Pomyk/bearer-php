<?php

namespace Bearer;

class Client {

    static $VERSION = "1.1.0";

    protected $bearerApiKey;
    protected $timeout = 5;
    protected $connectTimeout = 5;
    protected $host = 'https://int.bearer.sh';
    protected $path = '/api/v4/functions/backend';

    public function __construct ($bearerApiKey, $timeout = 5, $connectTimeout = 5) {
        $this->setApiKey($bearerApiKey);
        $this->setTimeout($timeout);
        $this->setConnectTimeout($connectTimeout);
        return $this;
    }

    public function setApiKey ($apiKey) {
        $this->bearerApiKey = $apiKey;
        return $this;
    }

    public function setHost ($host) {
        $this->host = $host;
        return $this;
    }

    public function setTimeout ($timeout) {
        $this->timeout = $timeout;
        return $this;
    }

    public function setConnectTimeout ($connectTimeout) {
        $this->connectTimeout = $connectTimeout;
        return $this;
    }

    public function integration ($id) {
        return new Integration(
            $id,
            $this->bearerApiKey,
            $this->host . $this->path,
            $this->timeout,
            $this->connectTimeout
        );
    }
}
