<?php

namespace Bearer;

class Integration {

    protected $config = [];
    protected $authId;
    protected $setupId;

    public function __construct($integrationId, $bearerApiKey, $baseUrl, $timeout = 5, $connectTimeout = 5) {
        $this->config = [
            "integrationId" => $integrationId,
            "bearerApiKey" => $bearerApiKey,
            "baseUrl" => $baseUrl,
            "timeout" => $timeout ?? 5,
            "connectTimeout" => $connectTimeout ?? 5
        ];
    }

    /* Handle users authentication */

    public function auth($authId) {
        $this->authId = $authId;
        return $this;
    }

    public function setup($setupId) {
        $this->setupId = $setupId;
        return $this;
    }

    public function authenticate () { return $this->auth(func_get_args()); } // alias


    /* Handle configuration */

    protected function getConfig () {
        $config = $this->config;

        if ($this->authId) {
            $config['authId'] = $this->authId;
        }

        if ($this->setupId) {
            $config['setupId'] = $this->setupId;
        }
        return $config;
    }

    /* Main requests methods */

    public function get($path, $params = []) { return $this->request('GET', $path, $params); }
    public function post($path, $params = []) { return $this->request('POST', $path, $params); }
    public function put($path, $params = []) { return $this->request('PUT', $path, $params); }
    public function delete($path, $params = []) { return $this->request('DELETE', $path, $params); }
    public function head($path, $params = []) { return $this->request('HEAD', $path, $params); }

    public function request($method, $path = '', $params = []) {
        $path = "bearer-proxy" . ($path[0] !== "/" ? "/" : "") . $path;

        $request = new Request($method, $path, $params, $this->getConfig());
        return $request->getResponse();
    }

    /* Invoke a user's function */

    public function invoke ($functionName, $params = []) {
        $request = new Request("POST", $functionName, $params, $this->getConfig());
        return $request->getResponse();
    }

}
