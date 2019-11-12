<?php
namespace Bearer\Tests;

use Bearer\RequestHandler;

class RequestHandlerTest extends \PHPUnit\Framework\TestCase
{
    private $testConfig;
    private $testIntegration;

    public function setUp(): void
    {
        $this->testConfig = require '_config.php';
    }

    public function testMakesGetRequest() {
        $testConfig = $this->testConfig;
        $testConfig['baseUrl'] = 'https://example.org';

        $handler = new RequestHandler($testConfig);
        $response = $handler->execute('GET', '/', []);

        $this->assertNotNull($response->getBody());
        $this->assertNotEmpty($response->getHeaders());
        $this->assertEquals(404, $response->getStatusCode());
    }

}

