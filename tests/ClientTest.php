<?php
namespace Bearer\Tests;
use Bearer;

require '_config.php';

class ClientTest extends \PHPUnit_Framework_TestCase
{
    private $testClient;
    private $testConfig;

    public function setUp()
    {
        global $config;
        $this->testConfig = $config;

        $this->testClient = new Bearer\Client($this->testConfig['bearerApiKey']);
    }

    public function testDefaultHost()
    {
        $this->assertAttributeEquals($this->testConfig['host'], "host", $this->testClient);
    }

    public function testDefaultPath()
    {
        $this->assertAttributeEquals($this->testConfig['path'], "path", $this->testClient);
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