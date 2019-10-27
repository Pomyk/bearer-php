<?php

namespace Bearer;

class Request
{
    private $method;
    private $path;
    private $params; // request parameters (headers, query or body)
    private $config;
    private $logger;
    private $curlHandle;

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
        $this->path = ltrim($path, '/');
        $this->params = $params;
        $this->config = $config;
        $this->logger = new \Monolog\Logger("bearer");
        $this->logger->pushHandler(new \Monolog\Handler\ErrorLogHandler());

        list($url, $body, $headers) = $this->prepare();
        $this->make($url, $body, $headers, 0);

        return $this;
    }

    public function __destruct() {
        if (!is_null($this->curlHandle)) {
            curl_close($this->curlHandle);
        }
    }

    protected function shouldRetry($error, $httpStatus, $numRetries, $options)
    {
        if (!empty($options['maxNetworkRetries'])) {
            $maxRetries = $options['maxNetworkRetries'];
            if ($numRetries >= $maxRetries) {
                return false;
            }
        } else {
            return false;
        }
        // retry on timeout or connect error
        if (in_array($error, [CURLE_OPERATION_TIMEOUTED, CURLE_COULDNT_CONNECT])) {
            return true;
        }
        if ($httpStatus >= 500) {
            return true;
        }
        return false;
    }

    protected function prepare()
    {
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

        return array($url, $body, $headers);
    }

    protected function make($url, $body, $headers, $retries = 0)
    {
        while (true) {
            // Make request
            $curl = $this->initCurl();
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
                "sending request ".
                "url: '" . $url . "'," .
                "method: '" . $this->method . "'," .
                "body: " . $body . "," .
                "headers: " . json_encode($headers) . "," .
                "http_client_settings: " . json_encode($httpClientSettings)
            );

            $this->response = curl_exec($curl);
            $error = curl_errno($curl);
            $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($error == CURLE_OK && isset($responseHeaders["bearer-request-id"])) {
                $this->logger->info("request id: " . $responseHeaders["bearer-request-id"][0]);
            }
            $shouldRetry = $this->shouldRetry($error, $httpStatus, $retries, $this->config['options']);
            if ($shouldRetry) {
                $retries += 1;
                $sleepTime = $this->sleepTime($numRetries, $this->config['options']);
                usleep(intval($sleepTime*1000000));
            } else {
                break;
            }
        }
        if (!$this->response) {
            throw new \Exception("\nThe Bearer client wasn't able to perform the request to $url.\nPlease check you are connected to the internet before trying again.\n");
        }

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

    protected function initCurl() {
        if (is_null($this->curlHandle)) {
            $this->curlHandle = curl_init();
        } else {
            curl_reset($this->curlHandle);
        }
        return $this->curlHandle;
    }

    protected function sleepTime($numRetries, array $options) {
        $initialNetworkRetryDelay = $options['initialNetworkRetryDelay'];
        $maxNetworkRetryDelay = $options['maxNetworkRetryDelay'];
        // exponential backoff with a limit of $maxNetworkRetryDelay
        $sleepSeconds = min(
            $initialNetworkRetryDelay * 1.0 * pow(2, $numRetries - 1),
            $maxNetworkRetryDelay
        );
        // Apply some jitter by randomizing the value in the range of
        // ($sleepSeconds / 2) to ($sleepSeconds).
        $sleepSeconds *= 0.5 * (1 + mt_rand()/mt_getrandmax());

        // But never sleep less than the base sleep seconds.
        $sleepSeconds = max($initialNetworkRetryDelay, $sleepSeconds);
        return $sleepSeconds;
    }

}
