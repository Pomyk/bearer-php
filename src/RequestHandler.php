<?php

namespace Bearer;

class RequestHandler
{
    private $config;
    private $logger;

    private $retryStrategy;
    private $curlClient;

    public function __construct($config)
    {
        if (!array_key_exists("secretKey", $config)) {
            throw new \Exception('Bearer was unable to perform the API call. Your Bearer API Key is missing.');
        }

        if (!array_key_exists("integrationId", $config)) {
            throw new \Exception('Bearer was unable to perform the API call. The integration ID is missing.');
        }

        if (isset($config['logger']) && is_object($config['logger'])) {
            $this->logger = $config['logger'];
        }
        $this->config = $config;
        $this->updateCurlSettings();
        $this->retryStrategy = new RetryStrategy($config);

        return $this;
    }

    public function execute($method, $path, $params)
    {
        if (!in_array($method, ['HEAD', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
            throw new \Exception("Bearer was unable to perform the API call. Unsupported request method.");
        }

        list($url, $body, $headers) = $this->prepare($method, $path, $params);
        return $this->make($method, $url, $body, $headers, 0);
    }

    public function __destruct()
    {
        if (is_object($this->curlClient)) {
            $this->curlClient->close();
        }
    }

    protected function prepare($method, $path, $params)
    {
        // Work with provided headers
        $headers = array_key_exists('headers', $params) ? $params['headers'] : false;

        if (is_array($headers)) {
            $preheaders = [];
            foreach ($headers as $key => $value) {
                array_push($preheaders, $key.': '.$value);
            }
            $headers = $preheaders;
        } else {
            $headers = [];
        }

        // Prepare query parameters
        $query = "";
        if (array_key_exists("query", $params) && is_array($params["query"])) {
            $querystring = http_build_query($params["query"]);
            $query = (preg_match("/\?/", $this->path) ? "&" : "?") . $querystring;
        }

        // Prepare body content (if any)
        // TODO - Allow other Content-Types than JSON
        $body = "";
        if (array_key_exists("body", $params) && is_array($params["body"])) {
            $parsedbody = json_encode($params["body"]);
            $body = $parsedbody;
            array_push($headers, 'Content-Type: application/json');
            array_push($headers, 'Content-Length: ' . strlen($body));
        }

        // Prepare url
        $baseUrl = $this->config['baseUrl'];
        $integrationId = $this->config['integrationId'];
        $url = $baseUrl . "/" . $integrationId . "/" . ltrim($path, '/') . $query;

        return array($url, $body, $headers);
    }

    protected function make($method, $url, $body, $headers, $retries = 0)
    {
        $this->curlClient = $this->getCurlClient();
        while (true) {
            // Make request
            $this->curlClient->init();
            $this->curlClient->setOptArray($this->config['httpClientConfig']);
            $this->curlClient->setOpt(CURLOPT_USERAGENT, $this->getUserAgent());
            $this->curlClient->setOpt(CURLOPT_URL, $url);
            $this->curlClient->setOpt(CURLOPT_CUSTOMREQUEST, $method);
            $this->curlClient->setOpt(CURLOPT_HTTPHEADER, $headers);
            $this->curlClient->setOpt(CURLOPT_POSTFIELDS, $body);
            $this->curlClient->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->curlClient->setOpt(
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

            $this->log('debug',
                "sending request ".
                "url: '" . $url . "'," .
                "method: '" . $method . "'," .
                "body: " . $body . "," .
                "headers: " . json_encode($headers) . "," .
                "http_client_settings: " . json_encode($this->config['httpClientConfig'])
            );

            $this->response = $this->curlClient->exec();
            $error = $this->curlClient->errno();
            $httpStatus = $this->curlClient->getinfo(CURLINFO_HTTP_CODE);

            if ($error == CURLE_OK && isset($responseHeaders["bearer-request-id"])) {
                $this->log('info', "request id: " . $responseHeaders["bearer-request-id"][0]);
            }
            $shouldRetry = $this->retryStrategy->shouldRetry($error, $httpStatus, $retries);
            if ($shouldRetry) {
                $retries += 1;
                $sleepTime = $this->retryStrategy->sleepTime($numRetries);
                usleep(intval($sleepTime*1000000));
            } else {
                break;
            }
        }
        if (!$this->response) {
            throw new \Exception("\nThe Bearer client wasn't able to perform the request to $url.\nPlease check you are connected to the internet before trying again.\n");
        }
        return new Response($this->response, $httpStatus, $responseHeaders);
    }

    protected function getCurlClient()
    {
        return new Curl();
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

    protected function log($level, $message)
    {
        if (!is_object($this->logger)) {
            return;
        }
        $this->logger->log($level, $message);
    }

    protected function updateCurlSettings()
    {
        if (empty($this->config['httpClientConfig'][CURLOPT_CONNECTTIMEOUT]) && !empty($this->config['connectTimeout'])) {
            $this->config['httpClientConfig'][CURLOPT_CONNECTTIMEOUT] = $this->config['connectTimeout'];
        }
        if (empty($this->config['httpClientConfig'][CURLOPT_TIMEOUT]) && !empty($this->config['timeout'])) {
            $this->config['httpClientConfig'][CURLOPT_TIMEOUT] = $this->config['timeout'];
        }
    }

}
