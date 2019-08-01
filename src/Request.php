<?php

namespace Bearer;

class Request {

    private $method;
    private $path;
    private $params; // request parameters (headers, query or body)
    private $config;

    public function __construct($method, $path, $params, $config)
    {

        if (is_null($config['bearerApiKey'])) {
            throw new \Exception('Unable to perform the request. Missing the Bearer API Key.');
        }

        if (!in_array($method, ['HEAD','GET','POST','PUT','PATCH','DELETE'])) {
            throw new \Exception("Unable to perform the request. Unsupported request method.");
        }

        $this->method = $method;
        $this->path = $path;
        $this->params = $params;

        $this->config = $config;
        $this->make();

        return $this;
    }

    protected function make()
    {
        $curl = curl_init();

        // Work with provided headers
        $headers = $this->params['headers'];
        
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
        $setupId = $this->config["setupId"];
        $authId = $this->config["authId"];

        array_push($headers, "Authorization: $bearerApiKey");

        if (!is_null($setupId)) {
            array_push($headers,"Bearer-Setup-Id: $setupId");
        }

        if (!is_null($authId)) {
            array_push($headers,"Bearer-Auth-Id: $authId");
        }

        // Prepare query parameters
        $query = "";
        if (is_array($this->params["query"])) {
            $querystring = http_build_query($this->params["query"]);
            $query = (preg_match("/\?/", $this->path) ? "&" : "?") . $querystring;
        }

        // Prepare body content (if any)
        // TODO - Allow other Content-Types than JSON
        $body = $this->params["body"];
        if (is_array($body)) {
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