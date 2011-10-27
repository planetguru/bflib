<?php

function customAutoload( $class ){
	$classpath = '/export/bflib/classes/'.$class.'.class.php';
	if (file_exists( $classpath )){
		require_once($classpath);
		return;
	}
}

spl_autoload_register('customAutoload');

class betfairLoggerTest extends PHPUnit_Framework_TestCase {

	private $logger;
	private $logfile = '/tmp/logtestfile';
	private $message = "\nthis is a test";

	protected function setUp() {
 		$this->logger = betfairLogger::getInstance();
		$this->logger->logHandle = fopen($this->logfile,'w');
	}

	public function testbetfairLogger () {
		$this->logger->log($this->message);
		$file = $this->logfile;
		$data = file($file);
		$line = $data[count($data)-1];
		$this->assertTrue(TRUE, $line == $this->message);
	}

	protected function tearDown(){
		$this->logger = NULL;
	}

}
?>
