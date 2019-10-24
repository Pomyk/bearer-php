<?php

return [
    'host' => 'https://proxy.bearer.sh',
    'secretKey' => 'foo:bar',
    'integrationId' => '1a2b3c',
    'httpClientSettings' => [
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 5
    ],
    'options' => [
        'maxNetworkRetries' => 0,
        'maxNetworkRetryDelay' => 2,
        'initialNetworkRetryDelay' => 0.5,
    ],
];
