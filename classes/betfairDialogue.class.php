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
	private $soapOptions;
	private $sessionToken = '';
        private $data;
	private $logger;

	private static $instance;

	public $globalMethods = array();
	public $exchangeMethods = array();
	public $isConnected = false;
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
	
		/* initialize the logger for this instance */
		$logger = betfairLogger::getInstance();
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
	* perform further processing on the soap data before it gets passed to the exchange.
	* This includes tasks like building object ids into the request object, or setting 
	* a currency code, or loading constants into data objects.
	*
	* operates directly upon $this->data 
	*
	* @todo collapse to a switch statement; move data preparation to a helper class
	*
	*/
	public function prepareRequestData( ){

	}


	/**
	* perform further processing on the soap data before it gets passed to the view
	* this includes tasks like parsing market price data into structured form. It might
	* make sense to move these out into seperate methods of a processor library if it gets
	* too unwieldly.
	*
	* @todo collapse to a switch statement; move data preparation to a helper class
	* @param array $datain soap response object as returned from the active client
	* @return array $dataout soap response object enhanced with additional/formatted data
	*
	*/
	public function prepareResponseData( $datain ){
		$dataout = $datain;

		/* if this contains compressed market data, then decompress and add a new marketData node */
		if(true === isset($datain->Result->completeMarketPrices)){
			$completeMarketPrices = $datain->Result->completeMarketPrices;
			$market = new stdClass;
			$market->marketId = substr($completeMarketPrices, 0, stripos($completeMarketPrices,'~'));
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
					if(isset($priceChunk[1])){
						$price->backAmountAvailable = $priceChunk[1];
					}
					if(isset($priceChunk[2])){
						$price->layAmountAvailable = $priceChunk[2];	
					}
					if(isset($priceChunk[3])){
						$price->BSPBackAvailableAmount = $priceChunk[3];
					}
					if(isset($priceChunk[4])){
						$price->BSPLayAvailableAmount = $priceChunk[4];
					}
					if(is_numeric($price->odds)){
						$runner->prices[]=$price;
					}
				}
	
				/* extract all the runner dada  */
				$infoElements = explode('~',$informationRow);
				if(isset($infoElements[0])){
					$runner->selectionId = $infoElements[0];
				}
				if(isset($infoElements[2])){
					$runner->orderIndex = $infoElements[1];
				}
				if(isset($infoElements[2])){
					$runner->amountMatched = $infoElements[2];
				}
				if(isset($infoElements[3])){
					$runner->lastPriceMatched = $infoElements[3];
				}
				if(isset($infoElements[4])){
					$runner->handicap = $infoElements[4];
				}
				if(isset($infoElements[5])){
					$runner->reductionFactor = $infoElements[5];
				}
				if(isset($infoElements[6])){
					$runner->vacant = $infoElements[6];
				}
				if(isset($infoElements[7])){
					$runner->asianLineId = $infoElements[7];
				}
				if(isset($infoElements[8])){
					$runner->farSPPrice = $infoElements[8];
				}
				if(isset($infoElements[9])){
					$runner->nearSPPrice = $infoElements[9];
				}
				if(isset($infoElements[10])){
					$runner->actualSPPrice = $infoElements[10];
				}
				$dataout->allRunnerData[]=$runner;
			}
		}else if(true === isset($datain->Result->marketData)){
			$marketItems = array();
			$marketItems = explode(':',$datain->Result->marketData);
			$dataout->Result->marketDataItems = array();

			foreach($marketItems as $marketItem){
				//$market = new stdClass;
				$marketElements = explode('~',$marketItem);
				$market->marketId = $marketElements[0];
				if(isset($marketElements[1])){
					$market->marketName = $marketElements[1];
				}
				if(isset($marketElements[2])){
					$market->marketType = $marketElements[2];
				}
				if(isset($marketElements[3])){
					$market->marketStatus = $marketElements[3];
				}
				if(isset($marketElements[4])){
					$market->eventDate = $marketElements[4];
				}
				if(isset($marketElements[5])){
					$market->menuPath = $marketElements[5];
				}
				if(isset($marketElements[6])){
					$market->eventHierarchy = $marketElements[6];
				}
				if(isset($marketElements[7])){
					$market->betDelay = $marketElements[7];
				}
				if(isset($marketElements[8])){
					$market->exchangeId = $marketElements[8];
				}
				if(isset($marketElements[9])){
					$market->ISOCountryCode = $marketElements[9];
				}
				if(isset($marketElements[10])){
					$market->lastRefresh = $marketElements[10];
				}
				if(isset($marketElements[11])){
					$market->numberOfRunners = $marketElements[11];
				}
				if(isset($marketElements[12])){
					$market->numberOfWinners = $marketElements[12];
				}
				if(isset($marketElements[13])){
					$market->totalAmountMatched = $marketElements[13];
				}
				if(isset($marketElements[14])){
					$market->BSPMarket = $marketElements[14];
				}
				if(isset($marketElements[15])){
					$market->turningInPlay = $marketElements[15];
				}
				if(true == is_numeric($market->marketId)){
					//$dataout->Result->marketDataItems[]=$market;
				}
			}
		}
		if(isset($market)){
			$dataout->Result->marketDataItems[]=$market;
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
