<?php

$config = [
    'host' => 'https://int.bearer.sh',
    'secretKey' => 'foo:bar',
    'integrationId' => '1a2b3c',
    'httpClientSettings' => [
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 5
    ]
];
