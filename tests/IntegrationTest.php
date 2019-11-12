<?php

namespace Bearer\Tests;

use Bearer;

class IntegrationTest extends \PHPUnit\Framework\TestCase
{
    private $testIntegration;
    private $testConfig;

    public function setUp(): void
    {
        $this->testConfig = require '_config.php';

        $client = new Bearer\Client($this->testConfig['secretKey'], $this->testConfig['options']);
        $this->testIntegration = $client->integration($this->testConfig['integrationId']);
    }

    public function testCanMakeGetRequest()
    {
        $this->assertTrue(
            method_exists($this->testIntegration, 'get'),
            'Class does not have method get()'
        );
    }

    public function testCanMakePostRequest()
    {
        $this->assertTrue(
            method_exists($this->testIntegration, 'post'),
            'Class does not have method post()'
        );
    }

    public function testCanMakePutRequest()
    {
        $this->assertTrue(
            method_exists($this->testIntegration, 'put'),
            'Class does not have method put()'
        );
    }

    public function testCanMakeDeleteRequest()
    {
        $this->assertTrue(
            method_exists($this->testIntegration, 'delete'),
            'Class does not have method delete()'
        );
    }

    public function testCanMakeHeadRequest()
    {
        $this->assertTrue(
            method_exists($this->testIntegration, 'head'),
            'Class does not have method head()'
        );
    }
}
