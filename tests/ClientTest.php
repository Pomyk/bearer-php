<?php

namespace Bearer\Tests;

use Bearer\Client;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    private $testClient;
    private $testConfig;

    public function setUp(): void
    {
        $this->testConfig = require '_config.php';

        $this->testClient = new Client($this->testConfig['secretKey'], $this->testConfig['options']);
    }

    public function testSetUpIntegration()
    {
        $integrationId = $this->testConfig['integrationId'];
        $this->assertInstanceOf('Bearer\Integration', $this->testClient->integration($integrationId));
    }

    public function testErrorNoApiKey()
    {
        $this->expectException(\ArgumentCountError::class);
        new Client();
    }

}
