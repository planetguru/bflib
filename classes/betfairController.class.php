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

    @author Chris Lacy-Hulbert chris@spiration.co.uk
*/


/**
* The betfairController class loosely acts as an application controller in MVC-speak. This class handles
* interaction between the betfairDialogue and the calling view/application. The controller is also 
* responsible for discerning context and eventually serving the rendered view output to the user
*
*/
class betfairController {
	private $context = '';
	private $logger;

	public $data = array();
	private $itemId;
	public $soapMessage;

	/**
	* construct controller object
	*
	* @param none
	* @return none
	*/
        public function __construct( ){ 
		/* start preparing the virgin soap message */
		$this->soapMessage = array();
		$this->soapMessage['request']=array();

		/* initialize a logger object */
		$this->logger = betfairLogger::getInstance();

		/* set up the Soap dialoge */
		$this->prepareDialogue();

		//  If I have a sessionToken cached, skip the login
		$sessionTokenCache = betfairCache::fetch('sessionToken');
		if( FALSE == $sessionTokenCache ){
			/* otherwise, log this controller in before we do anything else */
			$loginresult = $this->login();
			$sessionToken = $loginresult->Result->header->sessionToken;
			$this->dialogue->setSessionToken($sessionToken);
		}

		/* at this point, I have either logged in and retrieved a sesssion, or I have a sessionToken
		* in cache and so will add it to the soap message in the contructRequest method.  
		*
		* @TODO The betfairDialogue should be extended
		* to check for session expiries in all responses and purge the sessionToken cache element, which 
		* would then force a re-login when this controller gets reinstantiated
		*/
		
	}

	/**
	* set the context ready to pass into the dialogue object
	*
	*/
	public function setContext( $contextString ){
		$this->context = $contextString;
	}

	/**
	* get the context 
	*
	*/
	public function getContext(){
		return($this->context);
	}

	/**
	* set the itemid ready to pass into the dialogue object
	*
	*/
	public function setItemId( $item ){
		$this->itemId = $item;
	}

	/**
	* set up the dialogue object, request function lists from the service WSDLs,
	*
	*/
	public function prepareDialogue(){
		$this->dialogue = betfairDialogue::getInstance();
		$this->dialogue->connect();	
		$this->dialogue->getFunctionsFromWSDL();
	}

	/*
	* Wrap the execute function, with a check for the method in the dialogue's associated wsdl files
	* For methods which aren't in context, provide bespoke logic/combinations
	*
	* @param none
	* @return object $soapResult
	*/
	public function run(){
		/* * check context.  If it doesn't live in the dialogue wsdls, then fall into combiner code */
		if( $this->dialogue->hasContext($this->context)){
			$soapResult = $this->execute();
		}else{
			/* special handlers for non betfair-native method calls */
			switch($this->context){
				case 'getRunnersAndPrices':  //custom
					/* combiner to get market data (inc runner names) and all prices */
					$this->context = 'getMarket';
					$marketSoapResult = $this->execute();	
					$this->context = 'getCompleteMarketPricesCompressed';
					$runnerSoapResult = $this->execute();	

					/* combiner logic */
					foreach($runnerSoapResult->allRunnerData as &$selection){
						/* capture the name of this runner */
						foreach($marketSoapResult->Result->market->runners->Runner as &$runner){
							if($runner->selectionId == $selection->selectionId){
								$selection->name=$runner->name;
							}
						}
					}

					return($runnerSoapResult);
					break;

				case 'getRunnersAndTopPrices':  //custom
					/* combiner to get market data (inc runner names) and top back prices */
					/* also retrieves parent event id, breadcrumb string and event heirarchy */

					$this->context = 'getMarket';
					$marketSoapResult = $this->execute();	
					// If this result bears marketStatus data, handle it appropriately
					if(isset($marketSoapResult->Result->market->marketStatus)){
						$this->logger->log($marketSoapResult->Result->market->marketStatus);
						if($marketSoapResult->Result->market->marketStatus == betfairConstants::ERROR_EVENT_CLOSED){
							throw new marketClosedException('market is closed');
						}
						if($marketSoapResult->Result->market->marketStatus == betfairConstants::ERROR_EVENT_SUSPENDED){
							throw new marketSuspendedException('market is suspended');
						}
						if($marketSoapResult->Result->market->marketStatus == betfairConstants::ERROR_EVENT_INACTIVE){
							throw new marketInactiveException('market is inactive');
						}
					}
					$this->context = 'getCompleteMarketPricesCompressed';
					$runnerSoapResult = $this->execute();	
					if($runnerSoapResult->Result->errorCode == betfairConstants::ERROR_EVENT_CLOSED){
						throw new marketClosedException('market is closed');
					}
					if($runnerSoapResult->Result->errorCode == betfairConstants::ERROR_EVENT_SUSPENDED){
						throw new marketSuspendedException('market is suspended');
					}
					if($runnerSoapResult->Result->errorCode == betfairConstants::ERROR_EVENT_INACTIVE){
						throw new marketInactiveException('market is inactive');
					}
					$runnerSoapResult->Result->marketDataItems[0]->marketName = $marketSoapResult->Result->market->name;
					$runnerSoapResult->Result->marketDataItems[0]->marketTime = $marketSoapResult->Result->market->marketTime;

					/* add event heirarchy/breadcrumb elements to result object*/
					$runnerSoapResult->Result->eventHierarchy = $marketSoapResult->Result->market->eventHierarchy;
					$runnerSoapResult->Result->menuPath = $marketSoapResult->Result->market->menuPath;
					$runnerSoapResult->Result->parentEventId = $marketSoapResult->Result->market->parentEventId;

					/* turn the path items to an array, remove zero'th element from path items and eventid hierarchy */
					$breadcrumbNodes = explode("\\",$runnerSoapResult->Result->menuPath);
					$del = array_shift($breadcrumbNodes);
					$del = array_shift($runnerSoapResult->Result->eventHierarchy->EventId);
					$runnerSoapResult->Result->breadcrumbNodes = $breadcrumbNodes;

					/* combiner logic */
					foreach($runnerSoapResult->allRunnerData as &$selection){
						/* capture the name of this runner */
						foreach($marketSoapResult->Result->market->runners->Runner as &$runner){
							if($runner->selectionId == $selection->selectionId){
								$selection->name=$runner->name;
							}
						}
                        			$savedPrice = 0; $savedAmount = 0;
						foreach($selection->prices as $price){
							if(true === ($price->backAmountAvailable > betfairConstants::MINIMUM_BET)){
								$price->odds = (string)$price->odds;
								if($price->odds > $savedPrice){
									$savedPrice = $price->odds;
									$savedAmount = $price->backAmountAvailable;
								}
							}
						}
						$selection->topPrice = $savedPrice;
						$selection->topPriceVol = $savedAmount;

						/* remove the list of prices. I only want the top ones */
						unset($selection->prices);
					}
					return($runnerSoapResult);
					break;

				default:
					throw invalidMethodException();
					break;
			}	
		}
		return ($soapResult);
	}

	/**
	* Pass requet through to betfairDialogue instance
	* 
	* @param none
	* @return object $soapResult
	*/
	public function execute(){
		/* if there is no context, there is nothing to run */
		if( false === empty( $this->context )){
			$this->data = $this->constructRequestData($this->context, $this->itemId);
			$this->dialogue->setContext($this->context);
			$this->dialogue->setData($this->data);
			$soapResult = $this->dialogue->execute();
			$soapResult = $this->dialogue->prepareResponseData($soapResult);
		}
		if(true === isset($soapResult) && false === empty($soapResult)){
			return($soapResult);
		}
	}

	/*
	* Set up a 'login' request to be passed through the current dialogue
	* 
	* @return soapResult
	*/
	public function login(){
		$this->setContext('login');
		$this->dialogue->setContext('login');
		$this->dialogue->setData($this->constructRequestData('login', 0));  
		$soapResult = $this->dialogue->execute();
		return($soapResult);
	}

	/**
	* Add an element to the $this->soapMessage['request'] array.  This will most likely be called
	* from outside the framework and passed straight through. This is a useful way of adding
	* values beyond just the 'itemId', which would be determined at runtime rather than through
	* configuration
	*
	* @param $key the key name of the item to add to the request
	* @param $value the value for this key
	*
	*/
	public function addRequestElement($key, $value){
		//echo ('<pre>'); var_dump($this->soapMessage); echo ('</pre>');
		if(true === is_array($value)){
			$this->soapMessage['request'][$key] = array();
			$this->soapMessage['request'][$key] = $value;
		}else{
			$this->soapMessage['request'][$key] = $value;
		}
		//echo ('<pre>'); var_dump($this->soapMessage); echo ('</pre>');exit();
	}


	/** 
	* Based on the context of this request and the 'id' provided, set up request params to be passed
	* in the soap message by the dialogue object
	*
	* @param $id the id on which the method will be run. Usually an integer, but could be an array of ints
	* @param $url passed in for convenience in cases where the verb and target id are insufficient
	* @return the soapMessage array with fully constructed request and session data
	*
	* @todo move this into betfairDialogue as prepareRequestData; rename prepareData to prepareResponseData
	*/
	public function constructRequestData( $context, $id = ''){
		/* text the context and set parameters as necessary */
		switch($this->context){
			case 'login':
				$this->soapMessage['request']['username']=vendorConstants::USERNAME;
				$this->soapMessage['request']['password']=vendorConstants::PASSWORD;
				$this->soapMessage['request']['productId']=vendorConstants::PRODUCTID;
				$this->soapMessage['request']['vendorSoftwareId']=vendorConstants::VENDORID;
				$this->soapMessage['request']['locationId']=vendorConstants::LOCATIONID;
				$this->soapMessage['request']['ipAddress']=vendorConstants::IPADDRESS;
				break;

			case 'getAllMarkets':
				if( true === is_numeric( $id )){
					$this->soapMessage['request']['eventTypeIds'][] = $id;
				}
				break;
		
			case 'getMarket':
				$this->soapMessage['request']['marketId'] = $id;
				$this->soapMessage['request']['includeCouponLinks'] = false;
				$this->soapMessage['request']['currencyCode'] = betfairConstants::CURRENCY_CODE;
				break;

			case 'getCompleteMarketPricesCompressed':
				$this->soapMessage['request']['marketId'] = $id;
				$this->soapMessage['request']['currencyCode'] = betfairConstants::CURRENCY_CODE;
				break;

			case 'getEvents':	
				$this->soapMessage['request']['eventParentId']=$id;
				break;

			case 'getEvent':
				$this->soapMessage['request']['eventParentId']=$id;
				break;

			case 'getMarketPrices';
				$this->soapMessage['request']['marketId'] = $id;
				$this->soapMessage['request']['currencyCode'] = betfairConstants::CURRENCY_CODE;
				break;

			default:
				break;	
		}
	
		$this->soapMessage['request']['header']=array('clientStamp' => 0, 'sessionToken' => $this->dialogue->getSessionToken() );
		return($this->soapMessage);
	}
}
?>
