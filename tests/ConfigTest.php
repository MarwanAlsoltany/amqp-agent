<?php

namespace MAKS\AmqpAgent\Tests;

use MAKS\AmqpAgent\Tests\TestCase;
use MAKS\AmqpAgent\Config;
use MAKS\AmqpAgent\Exception\ConfigFileNotFoundException;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    public function setUp(): void
    {
        parent::setUp();
        $this->config = new Config();
    }

    public function tearDown(): void
    {
        parent::setUp();
        unset($this->config);
    }

    public function testConfigFileNotFoundExceptionIsRaisedViaConstructor()
    {
        $this->expectException(ConfigFileNotFoundException::class);
        $error = new Config('/path/to/no/where/none-existence.php');
    }

    public function testConfigFileNotFoundExceptionIsRaisedViaSetConfigPath()
    {
        $this->expectException(ConfigFileNotFoundException::class);
        $error = $this->config->setConfigPath('/path/to/no/where/none-existence.php');
    }

    public function testManipulatingConfigArrayViaPublicPropertyAccessAndAssignmentNotation()
    {
        $oldVhost = $this->config->connectionOptions['vhost'];
        $this->config->connectionOptions = ['vhost' => '/new'];
        $newVhost = $this->config->connectionOptions['vhost'];
        $this->assertEquals('/new', $newVhost);
        $this->assertNotEquals($newVhost, $oldVhost);
    }

    public function testGetConfigReturnsAnArrayAndHasAnExpectedToBeFoundKey()
    {
        $config = $this->config->getConfig();
        $this->assertIsArray($config);
        $this->assertArrayHasKey('prefix', $config);
    }

    public function testSetConfigResetsTheConfigAndReturnsTheNewArray()
    {
        $newConfig = ['key' => 'value'];
        $this->config->setConfig($newConfig);
        $curConfig = $this->config->getConfig();
        $this->assertArrayHasKey('key', $curConfig);
        $this->assertEquals($newConfig['key'], $curConfig['key']);
    }

    public function testGetConfigPath()
    {
        // Config class is instanciated with Config::DEFAULT_CONFIG_FILE_PATH as a path
        $path = $this->config->getConfigPath();
        $this->assertEquals(Config::DEFAULT_CONFIG_FILE_PATH, $path);
    }

    public function testSetConfigPathSetsANewPathAndRebuildsTheConfigObject()
    {
        $path = Config::DEFAULT_CONFIG_FILE_PATH;
        // Set a new path
        $this->config->setConfigPath($path);
        // Check if the new path is set
        $this->assertEquals($path, $this->config->getConfigPath());
        // Check if the configuration is rebuild
        $this->assertArrayHasKey('prefix', $this->config->getConfig());
    }

    public function testHasMethodReturnsAnExpectedValue()
    {
        $dummyConfig = ['val1' => 'test', 'val2' => ['valA' => 1, 'valB' => 2]];
        $this->config->setConfig($dummyConfig);
        $true1 = $this->config->has('val1');
        $this->assertTrue($true1);
        $true2 = $this->config->has('val2.valA');
        $this->assertTrue($true2);
        $false1 = $this->config->has('blabla');
        $this->assertFalse($false1);
        $false2 = $this->config->has('bla.bla');
        $this->assertFalse($false2);
    }

    public function testGetMethodReturnsAnExpectedValues()
    {
        $dummyConfig = ['val1' => 'test', 'val2' => ['valA' => 1, 'valB' => 2]];
        $this->config->setConfig($dummyConfig);
        $test1 = $this->config->get('val1');
        $this->assertEquals('test', $test1);
        $test2 = $this->config->get('val2.valB');
        $this->assertEquals(2, $test2);
    }

    public function testSetMethodSetsValuesAndGetMethodReturnsThem()
    {
        $dummyConfig = ['val1' => 'test', 'val2' => ['valA' => 1, 'valB' => 2]];
        $this->config->setConfig($dummyConfig);
        $this->config->set('val3.valZ', 'blabla');
        $test1 = $this->config->get('val3');
        $this->assertIsArray($test1);
        $test2 = $this->config->get('val3.valZ');
        $this->assertEquals('blabla', $test2);
    }

    public function testGetDefaultConfigReturnsDefaultConfig()
    {
        $default = $this->config->getDefaultConfig();
        $this->assertArrayHasKey('prefix', $default);
        $this->assertArrayHasKey('commandPrefix', $default);
        $this->assertArrayHasKey('commandSyntax', $default);
    }

    public function testCastingConfigObjectToString()
    {
        $path1 = Config::DEFAULT_CONFIG_FILE_PATH;
        $path2 = (string)$this->config;
        $this->assertEquals($path1, $path2);
    }
}
