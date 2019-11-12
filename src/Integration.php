<?php

namespace Bearer;

class Integration
{

    protected $config = [];
    protected $authId;
    protected $setupId;

    public function __construct($integrationId, $secretKey, $baseUrl, array $options)
    {
        if (empty($options['requestHandler'])) {
            $options['requestHandler'] = 'Bearer\RequestHandler';
        }
        $this->config = [
            "integrationId" => $integrationId,
            "secretKey" => $secretKey,
            "baseUrl" => $baseUrl,
        ] + $options;

        $handler = $this->config['requestHandler'];
        if (is_object($handler) || is_callable($handler)) {
            return $this->handler = $handler;
        } else if (is_string($handler) && class_exists($handler)) {
            $this->handler = new $handler($this->config);
        } else {
            throw \Exception('Invalid request handler');
        }
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

    protected function getHeaders()
    {
        $headers = [];

        if ($this->config['secretKey']) {
            $headers['Authorization'] = $this->config['secretKey'];
        }

        if ($this->authId) {
            $headers['Bearer-Auth-Id'] = $this->authId;
        }

        if ($this->setupId) {
            $headers['Bearer-Setup-Id'] = $this->setupId;
        }

        return $headers;
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

        $integrationHeaders = $this->getHeaders();
        $params['headers'] = array_merge(!empty($params['headers']) ? $params['headers'] : [], $integrationHeaders);

        if (is_object($this->handler)) {
            return $this->handler->execute($method, $path, $params);
        } else if (is_callable($handler)) {
            return call_user_func($handler, [$method, $path, $params, $this->config]);
        } else {
            throw \Exception('Invalid request handler');
        }
    }
}
