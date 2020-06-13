<?php

namespace MAKS\AmqpAgent\Test\Helper;

use MAKS\AmqpAgent\TestCase;
use MAKS\AmqpAgent\Helper\Logger;

class LoggerTest extends TestCase
{
    /**
     * @var Logger
     */
    private $logger;
    private $message;
    private $directory;
    private $filename;
    private $filePath;

    public function setUp(): void
    {
        parent::setUp();
        $this->message = 'This is a test!';
        $this->filename = 'logger-test';
        $this->directory = dirname(__DIR__, 2);
        $this->filePath = $this->directory . DIRECTORY_SEPARATOR . $this->filename . '.log';
        $this->logger = new Logger(
            $this->filename,
            $this->directory
        );
    }

    public function tearDown(): void
    {
        parent::setUp();
        unset($this->logger);
        unlink($this->filePath);
    }

    public function testGeneratingALogFileIfItDoesNotExistAndWritingToIt()
    {
        $this->logger->write($this->message);
        $this->assertFileExists($this->filePath);
    }

    public function testStaticCallingAndInsufficientCalling()
    {
        // this will raise a notic and a warning
        @$this->logger->log($this->message, null, null);
        $ture = Logger::log($this->message, $this->filename, $this->directory);
        $this->assertTrue($ture);
    }

    public function testGettersAndSetters()
    {
        $this->logger->write('To give unlink something to delete!');
        $filename = 'new-name';
        $directory = 'new/path';
        $this->logger->setFilename($filename);
        $this->logger->setDirectory($directory);
        $this->assertEquals($filename, $this->logger->getFilename());
        $this->assertEquals($directory, $this->logger->getDirectory());
    }
}
