<?php

namespace Bearer;

class Request {

    private $method;
    private $path;
    private $params; // request parameters (headers, query or body)
    private $config;
    private $timeout;
    private $connectTimeout;

    public function __construct($method, $path, $params, $config, $timeout = 5, $connectTimeout = 5)
    {

        if (!array_key_exists("bearerApiKey", $config)) {
            throw new \Exception('Bearer was unable to perform the API call. Your Bearer API Key is missing.');
        }

        if (!array_key_exists("integrationId", $config)) {
            throw new \Exception('Bearer was unable to perform the API call. The integration ID is missing.');
        }

        if (!in_array($method, ['HEAD','GET','POST','PUT','PATCH','DELETE'])) {
            throw new \Exception("Bearer was unable to perform the API call. Unsupported request method.");
        }

        $this->method = $method;
        $this->path = $path;
        $this->params = $params;

        $this->config = $config;
        $this->timeout = $timeout ?? 5;
        $this->connectTimeout = $connectTimeout ?? 5;
        $this->make();

        return $this;
    }

    protected function make()
    {
        $curl = curl_init();

        // Work with provided headers
        $headers = array_key_exists('headers', $this->params) ? $this->params['headers'] : false;

        if(is_array($headers)) {
            $preheaders = [];
            foreach ($headers as $key => $value) {
                $preheader = "Bearer-Proxy-" . $key . ": " . $value;
                array_push($preheaders, $preheader);
            }
            $headers = $preheaders;
        } else {
            $headers = [];
        }

        // Handle auth
        $bearerApiKey = $this->config["bearerApiKey"];
        array_push($headers, "Authorization: $bearerApiKey");

        if (array_key_exists("setupId", $this->config)) {
            array_push($headers,"Bearer-Setup-Id: " . $this->config["setupId"]);
        }

        if (array_key_exists("authId", $this->config)) {
            array_push($headers,"Bearer-Auth-Id: " . $this->config["authId"]);
        }

        // Prepare query parameters
        $query = "";
        if (array_key_exists("query", $this->params) && is_array($this->params["query"])) {
            $querystring = http_build_query($this->params["query"]);
            $query = (preg_match("/\?/", $this->path) ? "&" : "?") . $querystring;
        }

        // Prepare body content (if any)
        // TODO - Allow other Content-Types than JSON
        $body = "";
        if (array_key_exists("body", $this->params) && is_array($this->params["body"])) {
            $parsedbody = json_encode($this->params["body"]);
            $body = $parsedbody;
            array_push($headers,'Content-Type: application/json');
            array_push($headers,'Content-Length: ' . strlen($body));
        }

        // Prepare url
        $baseUrl = $this->config['baseUrl'];
        $integrationId = $this->config['integrationId'];
        $url = $baseUrl . "/" . $integrationId . "/" . $this->path . $query;

        // Make request
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->getUserAgent());
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);

        $this->response = curl_exec($curl);

        if(!$this->response){
            throw new \Exception("\nThe Bearer client wasn't able to perform the request to $url.\nPlease check you are connected to the internet before trying again.\n");
        }

        curl_close($curl);
        return $this;
    }

    public function getResponse() {
        return $this->response;
    }

    protected function getUserAgent() {
        return "Bearer for PHP (". \Bearer\Client::$VERSION ."); PHP (" . PHP_VERSION . ");";
    }

}
