<?php
/**
    Copyright Christopher Lacy-Hulbert 2009

    This file is part of Bflib.

    Bflib is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Bflib is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Bflib.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* Exception class 
*
* @author Chris Lacy-Hulbert <chris@spiration.co.uk>
*/
class betfairException extends Exception{
        private static $instance;
        public $logger;

        public function __construct(){
        	$this->logger = betfairLogger::getInstance();
        }

        public static function getInstance(){
                if(!isset(self::$instance)){
                        $c = __CLASS__;
                        self::$instance = new $c;
                }
		$this->logException();
                return self::$instance;
        }

        public function logException(){
		$errorMsg = 'Exception caught in '.$this-getFile().', line '.$this->getLine().':'.$this->getMessage();
    		$this->logger->log($errorMsg);
	}
}

/*
* marketClosedException
*
* @author Chris Lacy-Hulbert <chris@spiration.co.uk>
*/
class marketClosedException extends betfairException{}

/*
* soapErrorException
*
* @author Chris Lacy-Hulbert <chris@spiration.co.uk>
*/
class soapErrorException extends betfairException{}

/*
* marketSuspendedException
*
* @author Chris Lacy-Hulbert <chris@spiration.co.uk>
*/
class marketSuspendedException extends betfairException{}

/*
* marketInactiveException
*
* @author Chris Lacy-Hulbert <chris@spiration.co.uk>
*/
class marketInactiveException extends betfairException{}

?>
