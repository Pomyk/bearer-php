<?php

namespace Bearer\Tests;

use Bearer;

require '_config.php';

class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    private $testIntegration;
    private $testConfig;

    public function setUp()
    {
        global $config;
        $this->testConfig = $config;

        $client = new Bearer\Client($this->testConfig['secretKey']);
        $this->testIntegration = $client->integration($this->testConfig['integrationId']);
    }

    public function testSetAuth()
    {
        $authId = 'my-auth-id';

        $client = new Bearer\Client($this->testConfig['secretKey']);
        $integration = $client->integration($this->testConfig['integrationId']);
        $integration->auth($authId);

        $this->assertAttributeEquals($authId, "authId", $integration);
    }

    public function testSetSetup()
    {
        $setupId = 'my-setup-id';

        $client = new Bearer\Client($this->testConfig['secretKey']);
        $integration = $client->integration($this->testConfig['integrationId']);
        $integration->setup($setupId);

        $this->assertAttributeEquals($setupId, "setupId", $integration);
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
