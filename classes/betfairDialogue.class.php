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
* Serving as the Model for bflib (in MVC-speak), this class is responsible for querying
* the various exchange soap interfaces, loading WSDL files, assembling function calls,
* setting and getting session tokens, request data, and current method context. Built as a singleton
* since this can be called multiple times from the controller (esp when a login is performed prior to 
* an exchange operation).
*
* @author Chris Lacy-Hulbert <chris@spiration.co.uk>
* 
*/
class betfairDialogue {
	private $globalClient;
	private $exchangeClient;
	private $activeClient;
	public $globalMethods = array();
	public $exchangeMethods = array();
	private $soapOptions;
	private static $instance;
	public $isConnected = false;
	private $sessionToken = '';

        private $data;
        public $context = '';

        /**
        * return an instance of this class. Ensure only one instance exists for a given request
	*
	* @return betfairDialogue object instance
        */
        public static function getInstance(){
                if(!isset(self::$instance)){
                        $c = __CLASS__;
                        self::$instance = new $c;
                }
                return self::$instance;
        }

	/**
	* Set the method name to be passed into the soap call
	*
	* @param string $context method name
	**/
        public function setContext( $context ){
                $this->context = $context;
        }

	/**
	* Return the method name passed into the soap call
	*
	* @return $context method name
	**/
        public function getContext( ){
                return($this->context);
        }

	/**
	* Set data which will be passed into the soap call when we execute this dialogue
	*
	* @param array $data 
	**/
        public function setData( $data ){
                $this->data = $data;
        }

	/**
	* Return the data set for this soap call
	*
	* @return $data 
	**/
        public function getData( $data ){
                return($this->data);
        }

	/**
	* Instantiate a Soap client object and retrieve WSDL for each exchange
	*
	*/
	public function connect(){
		/* don't set up soap clients if this has already happened */
		if( true === $this->isConnected ){
			return;
		}
		$this->soapOptions = array('trace' => 1, 'exceptions' => 1);
		try {
			$this->globalClient = new SoapClient(betfairConstants::GLOBALSERVICE,$this->soapOptions);	
		} catch (SoapFault $fault) {
			$this->handleError($fault);	
			return;
		}
		try {
			$this->exchangeClient = new SoapClient(betfairConstants::EXCHANGESERVICE,$this->soapOptions);	
		} catch (SoapFault $fault) {
			$this->handleError($fault);	
			return;
		}
		$this->isConnected = true;
	}

	/*
	* Get method lists from the WSDL service descriptions for each exchange
	*
	*/
	public function getFunctionsFromWSDL(){
		$buffer = array();
		$funclist = $this->globalClient->__getFunctions();		
		foreach($funclist as $primative){
			$elements = array();
			$elements = explode(' ',$primative);
			$parts = explode('(',$elements[1]);
			$buffer[]=$parts[0];
			$this->globalMethods[]=$parts[0];
		}
		$this->globalMethods = $buffer;

		$buffer = array();
		$funclist = $this->exchangeClient->__getFunctions();		
		foreach($funclist as $primative){
			$elements = array();
			$elements = explode(' ',$primative);
			$parts = explode('(',$elements[1]);
			$this->exchangeMethods[]=$parts[0];
			$buffer[]=$parts[0];
		}
		$this->exchangeMethods = $buffer;
	}

	/**
	* Perform an operation against whichever betfair exchange hosts the current context/method
	* Also grab the session token returned in the soap header and store it for reuse 
	*
	* @return result object from soap client
	*/
	public function execute(){
		/* which interface does this method live in? */
		if(true === in_array($this->context, $this->globalMethods)){
			$this->activeClient = $this->globalClient;
		}else if(true === in_array($this->context, $this->exchangeMethods)){
			$this->activeClient = $this->exchangeClient;
		}else{
			die('invalid method: '.$this->context.' passed');
		}
		try {
			$method = $this->context;		
			$res = $this->activeClient->$method($this->data);
		} catch (SoapFault $fault) {
			$this->handleError($fault);	
		}
		/** assign session token to this dialogue's $sessionToken */
		$this->setSessionToken($res->Result->header->sessionToken);
		return( $res );
	}

	/**
	* perform further processing on the soap data before it gets passed to the view
	* this includes tasks like parsing market price data into structured form. It might
	* make sense to move these out into seperate methods of a processor library if it gets
	* too unwieldly.
	*
	* @param array $datain soap response object as returned from the active client
	* @return array $dataout soap response object enhanced with additional/formatted data
	*
	*/
	public function prepareData( $datain ){
		$dataout = $datain;

		/* if this contains compressed market data, then decompress and add a new marketData node */
		if(true === isset($datain->Result->completeMarketPrices)){
			$completeMarketPrices = $datain->Result->completeMarketPrices;
			$withoutMarketidAndDelay = ltrim(strstr($completeMarketPrices, '~'),'~');
			$withoutMarketidAndDelay = ltrim(strstr($withoutMarketidAndDelay, '~'),'~');
			$withoutMarketidAndDelay = ltrim($withoutMarketidAndDelay, ':');
			$runners = explode(':',$withoutMarketidAndDelay);

			$dataout->allRunnerData = array();
			foreach($runners as $row){
				$runner = new stdClass;
				list($info,$pricedata)=explode('|',$row);
				$informationRow = $info;

				$priceElements = array();
				$priceElements = explode('~',$pricedata);

				$priceChunks = array();
				$priceChunks = array_chunk($priceElements,5);		
				$runner->prices = array();

				foreach($priceChunks as $priceChunk){
					$price = new stdClass;
					$price->odds = $priceChunk[0];
					$price->backAmountAvailable = $priceChunk[1];
					$price->layAmountAvailable = $priceChunk[2];
					$price->BSPBackAvailableAmount = $priceChunk[3];
					$price->BSPLayAvailableAmount = $priceChunk[4];
					if(is_numeric($price->odds)){
						$runner->prices[]=$price;
					}
				}
	
				/* extract all the runner dada  */
				$infoElements = explode('~',$informationRow);
				$runner->selectionId = $infoElements[0];
				$runner->orderIndex = $infoElements[1];
				$runner->amountMatched = $infoElements[2];
				$runner->lastPriceMatched = $infoElements[3];
				$runner->handicap = $infoElements[4];
				$runner->reductionFactor = $infoElements[5];
				$runner->vacant = $infoElements[6];
				$runner->asianLineId = $infoElements[7];
				$runner->farSPPrice = $infoElements[8];
				$runner->nearSPPrice = $infoElements[9];
				$runner->actualSPPrice = $infoElements[10];
				$dataout->allRunnerData[]=$runner;
			}
		}

		return( $dataout );	
	}

	/**
	* Set the session token 
	*
	* @param string $val the session token retrieved from the soap response headers
	*
	*/
	public function setSessionToken( $val ){
		$this->sessionToken = $val;
	}

	/**
	* Retrieve the session token last received
	*
	* @return string representing lastest provided session token
	*/
	public function getSessionToken(){
		return($this->sessionToken);
	}

	/**
	* poor man's soapfault dump to stdout
	*
	* @param object $fault soapfault object
	* @todo modify this to make a betairLogger:: call
	*/
	private function handleError( SoapFault $fault ){
		if(true === betfairConstants::DEBUG_MODE ){
			betfairHelper::dumpAndExit($fault);
		}
	}
}
?>
