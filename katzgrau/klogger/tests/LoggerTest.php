<?php

require_once(__DIR__."/../../../autoload.php");

use Katzgrau\KLogger\Logger;

class LoggerTest extends PHPUnit_Framework_TestCase
{
    private $logPath;

    private $logger;
    private $errLogger;

    public function setUp()
    {
        $this->logPath = __DIR__.'/logs';
        $this->logger = new Logger($this->logPath, Logger::DEBUG, array ('flushFrequency' => 1));
        $this->errLogger = new Logger($this->logPath, Logger::ERROR, array (
            'extension' => 'log',
            'prefix' => 'error_',
            'flushFrequency' => 1
        ));
    }

    public function testAcceptsExtension()
    {
        $this->assertStringEndsWith('.log', $this->errLogger->getLogFilePath());
    }

    public function testAcceptsPrefix()
    {
        $filename = basename($this->errLogger->getLogFilePath());
        $this->assertStringStartsWith('error_', $filename);
    }

    public function testWritesBasicLogs()
    {
        $this->logger->log(Logger::DEBUG, 'This is a test');
        $this->errLogger->log(Logger::ERROR, 'This is a test');

        $this->assertTrue(file_exists($this->errLogger->getLogFilePath()));
        $this->assertTrue(file_exists($this->logger->getLogFilePath()));

        $this->assertLastLineEquals($this->logger);
        $this->assertLastLineEquals($this->errLogger);
    }


    public function assertLastLineEquals(Logger $logr)
    {
        $this->assertEquals($logr->getLastLogLine(), $this->getLastLine($logr->getLogFilePath()));
    }

    public function assertLastLineNotEquals(Logger $logr)
    {
        $this->assertNotEquals($logr->getLastLogLine(), $this->getLastLine($logr->getLogFilePath()));
    }

    private function getLastLine($filename)
    {
        $fp = fopen($filename, 'r');
        $pos = -2; // start from second to last char
        $t = ' ';
        $last_line = '';
        while($t !== "\n") {
            $res = fseek($fp, $pos, SEEK_END);
            if($res === -1) {
                break;
            }
            $t = fgetc($fp);
            $last_line = $t.$last_line;
            $pos--;
        }
        fclose($fp);

        return trim($last_line);
    }

    public function tearDown() {
        $loggerPath = $this->logger->getLogFilePath();
        $errLoggerPath = $this->errLogger->getLogFilePath();

        $this->logger = null;
        $this->errLogger = null;
        unlink($loggerPath);
        unlink($errLoggerPath);
    }
}
