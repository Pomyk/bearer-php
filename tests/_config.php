<?php

return [
    'host' => 'https://proxy.bearer.sh',
    'secretKey' => 'foo:bar',
    'integrationId' => '1a2b3c',
    'httpClientConfig' => [
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 5
    ],
    'options' => [
        'timeout' => 5,
        'connectTimeout' => 5,
        'maxNetworkRetries' => 0,
        'maxNetworkRetryDelay' => 2,
        'initialNetworkRetryDelay' => 0.5,
    ],
];
