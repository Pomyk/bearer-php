<?php

namespace Bearer;

class Client {

    static $VERSION = "1.0.1";

    protected $bearerApiKey;
    protected $host = 'https://int.bearer.sh';
    protected $path = '/api/v4/functions/backend';

    public function __construct ($bearerApiKey) {
        $this->setApiKey($bearerApiKey);
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

    public function integration ($id) {
        return new Integration($id, $this->bearerApiKey, $this->host . $this->path);
    }
}

