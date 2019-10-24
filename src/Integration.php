<?php

namespace Bearer;

class Integration
{

    protected $config = [];
    protected $authId;
    protected $setupId;

    public function __construct($integrationId, $secretKey, $baseUrl, $httpClientSettings, $options = [])
    {
        if ($options === []) {
            $options = Client::$defaultOptions;
        }
        $this->config = [
            "integrationId" => $integrationId,
            "secretKey" => $secretKey,
            "baseUrl" => $baseUrl,
            "httpClientSettings" => $httpClientSettings,
            "options" => $options,
        ];
    }


    /* Handle users authentication */

    public function auth($authId)
    {
        $this->authId = $authId;
        return $this;
    }

    public function setup($setupId)
    {
        $this->setupId = $setupId;
        return $this;
    }

    public function authenticate()
    {
        return $this->auth(func_get_args());
    } // alias


    /* Handle configuration */

    protected function getConfig()
    {
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

    public function get($path, $params = [])
    {
        return $this->request('GET', $path, $params);
    }
    public function post($path, $params = [])
    {
        return $this->request('POST', $path, $params);
    }
    public function put($path, $params = [])
    {
        return $this->request('PUT', $path, $params);
    }
    public function delete($path, $params = [])
    {
        return $this->request('DELETE', $path, $params);
    }
    public function head($path, $params = [])
    {
        return $this->request('HEAD', $path, $params);
    }

    private function request($method, $path = '', $params = [])
    {
        $path = ($path[0] !== "/" ? "/" : "") . $path;

        $request = new Request($method, $path, $params, $this->getConfig());
        return $request->getResponse();
    }
}
