<?php

namespace Bearer;

class Request
{
    private $method;
    private $path;
    private $params; // request parameters (headers, query or body)
    private $config;
    private $logger;
    public function __construct($method, $path, $params, $config)
    {

        if (!array_key_exists("secretKey", $config)) {
            throw new \Exception('Bearer was unable to perform the API call. Your Bearer API Key is missing.');
        }

        if (!array_key_exists("integrationId", $config)) {
            throw new \Exception('Bearer was unable to perform the API call. The integration ID is missing.');
        }

        if (!in_array($method, ['HEAD', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
            throw new \Exception("Bearer was unable to perform the API call. Unsupported request method.");
        }

        $this->method = $method;
        $this->path = $path;
        $this->params = $params;
        $this->config = $config;
        $this->logger = new \Monolog\Logger("bearer");
        $this->logger->pushHandler(new \Monolog\Handler\ErrorLogHandler());
        $this->make();

        return $this;
    }

    protected function make()
    {
        $curl = curl_init();

        // Work with provided headers
        $headers = array_key_exists('headers', $this->params) ? $this->params['headers'] : false;

        if (is_array($headers)) {
            $preheaders = [];
            foreach ($headers as $key => $value) {
                array_push($preheaders, $preheader);
            }
            $headers = $preheaders;
        } else {
            $headers = [];
        }

        // Handle auth
        $secretKey = $this->config["secretKey"];
        array_push($headers, "Authorization: $secretKey");

        if (array_key_exists("authId", $this->config)) {
            array_push($headers, "Bearer-Auth-Id: " . $this->config["authId"]);
        }

        if (array_key_exists("setupId", $this->config)) {
            array_push($headers, "Bearer-Setup-Id: " . $this->config["setupId"]);
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
            array_push($headers, 'Content-Type: application/json');
            array_push($headers, 'Content-Length: ' . strlen($body));
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
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt(
            $curl,
            CURLOPT_HEADERFUNCTION,
            function ($curl, $header) use (&$responseHeaders) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) // ignore invalid headers
                    return $len;

                $responseHeaders[strtolower(trim($header[0]))][] = trim($header[1]);

                return $len;
            }
        );

        $httpClientSettings = array_key_exists('httpClientSettings', $this->config) ? $this->config['httpClientSettings'] : false;

        if (is_array($httpClientSettings)) {
            foreach ($httpClientSettings as $key => $value) {
                curl_setopt($curl, $key, $value);
            }
        }

        $this->logger->debug(
            "sending request".
            "url: " . $url . "," .
            "method:" . $this->method . "," .
            "params:" . $query . "," .
            "body: " . $body . "," .
            "headers: " . json_encode($headers) . "," .
            "http_client_settings: " . json_encode($httpClientSettings)
        );

        $this->response = curl_exec($curl);

        $this->logger->info("request id: " . $responseHeaders["bearer-request-id"][0]);

        if (!$this->response) {
            throw new \Exception("\nThe Bearer client wasn't able to perform the request to $url.\nPlease check you are connected to the internet before trying again.\n");
        }

        curl_close($curl);
        return $this;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function getResponse()
    {
        return $this->response;
    }

    protected function getUserAgent()
    {
        return "Bearer for PHP (" . \Bearer\Client::$VERSION . "); PHP (" . PHP_VERSION . ");";
    }
}
