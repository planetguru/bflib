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
	private $message;

	protected function setUp() {
 		$this->logger = betfairLogger::getInstance();
		$this->message = "\nthis is a test " . rand();
	}

	public function testbetfairLogger () {
		$this->logger->log($this->message);
		$file = vendorConstants::LOGFILE;
		$data = file($file);
		$line = $data[count($data)-1];
		$this->assertTrue(TRUE, $line == $this->message);
	}

	protected function tearDown(){
		$this->logger = NULL;
	}

}
?>
