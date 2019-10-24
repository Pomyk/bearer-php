<?php
namespace Bearer\Tests;
use Bearer;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    private $testClient;
    private $testConfig;

    public function setUp()
    {
        $this->testConfig = require '_config.php';

        $this->testClient = new Bearer\Client($this->testConfig['secretKey']);
    }

    public function testDefaultHost()
    {
        $this->assertAttributeEquals($this->testConfig['host'], "host", $this->testClient);
    }

    public function testSetUpIntegration()
    {
        $integrationId = $this->testConfig['integrationId'];
        $this->assertInstanceOf('Bearer\Integration', $this->testClient->integration($integrationId));
    }

    public function testErrorNoApiKey()
    {
        $this->setExpectedException(\ArgumentCountError::class);
        new Bearer\Client();
    }

}
