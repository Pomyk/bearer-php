<?php
namespace Bearer\Tests;
use Bearer;
require '_config.php';

class RequestTest extends \PHPUnit_Framework_TestCase
{
    private $testConfig;
    private $testClient;
    private $testIntegration;

    public function setUp()
    {
        global $config;
        $this->testConfig = $config;
        
    }

    public function testMakesGetRequest() {
        $testConfig = $this->testConfig;
        $testConfig['baseUrl'] = 'https://example.org';

        $this->testClient = new Bearer\Request('GET', '/', [], $testConfig);

        $this->assertTrue(!is_null($this->testClient->getResponse()));
    }
    
}