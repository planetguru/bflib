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
* Betfair soap client library class. 
*
* Provides controller methods for handling betfair API sessions and envoking 'views'.
* This is likely to alter per-implementation. This version hangs heavily on the URL
* and is not flexible to different URL formats. 
*
* @author Chris Lacy-Hulbert chris@spiration.co.uk
*
*/

/**
* The betfairController class loosely acts as an application controller in MVC-speak. This class handles
* interaction between the betfairDialogue and the betfairView. The controller is also responsible for
* discerning context from the parent URI and eventually serving the rendered view output to the user
*
* @TODO - current thinking is to move this out of the framework, since it's really demo-specific (with
* all the URL handling stuff. Perhaps make this the betfairDemoRequestHandler, then package the bit which
* deals with structuring request data and manipulating the model etc as the betfairController 
*/
class betfairController {
	public $context = '';
	public $data = array();
	public $requestParts = array();
	public $itemId;
	public $soapMessage;

	/**
	* construct controller object
	*
	*/
        public function __construct( ){ 
		/** first login - currently on every call with forced 'login' context **/
		$this->prepareDialogue();
		$loginresult = $this->login();
		$this->soapMessage = array();
		$this->soapMessage['request']=array();
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

	/**
	* reset the controller between requests to remove legacy request elements
	*
	* @param none
	* @return none
	*/
	public function reset(){
		unset($this->dialogue);
		$this->prepareDialogue();
		$loginresult = $this->login();
		$this->soapMessage = array();
		$this->soapMessage['request']=array();
	}

	/**
	* Instantiate a betfairdialogue object, request function lists from the service WSDLs,
	* log in and pass a request through the client, according to the current request 'context'
	* Then hand over to the view class to render any output/soapresponse
	*
	*/
	public function run(){
		/* if there is no context, there is nothing to run */
		if( false === empty( $this->context )){
			$reqdata = $this->constructRequestData($this->context, $this->itemId);

			/** then call the required context if set **/ 
			if(!empty($this->context)){
				$this->data = $reqdata;
				$this->dialogue->setContext($this->context);
				$this->dialogue->setData($this->data);
				$soapResult = $this->dialogue->execute();
				$soapResult = $this->dialogue->prepareResponseData($soapResult);
			}
		}
		if(isset($soapResult) && false === empty($soapResult)){
			return($soapResult);
		}
	}

	/*
	* Set up a 'login' request to be passed through the current dialogue
	* 
	* @return soapResult
	*/
	public function login(){
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
		if(true === is_array($value)){
			$this->soapMessage['request'][$key] = array();
			$this->soapMessage['request'][$key] = $value;
		}else{
			$this->soapMessage['request'][$key] = $value;
		}
	}


	/** 
	* Based on the context of this request and the 'id' pulled from the request URI,
	* Set up some request parameters to be passed in the soap message by the dialogue object
	*
	* @param $context the context, or 'verb' of the current request
	* @param $id the id on which the method will be run. Usually an integer, but could be an array of ints
	* @param $url passed in for convenience in cases where the verb and target id are insufficient
	* @return the soapMessage array with fully constructed request and session data
	*
	* @todo move this into betfairDialogue as prepareRequestData; rename prepareData to prepareResponseData
	*/
	public function constructRequestData($context, $id){
		/* text the context and set parameters as necessary */
		switch($context){
			case 'login':
				$this->soapMessage['request']['username']=betfairConstants::USERNAME;
				$this->soapMessage['request']['password']=betfairConstants::PASSWORD;
				$this->soapMessage['request']['productId']=betfairConstants::PRODUCTID;
				$this->soapMessage['request']['vendorSoftwareId']=betfairConstants::VENDORID;
				$this->soapMessage['request']['locationId']=betfairConstants::LOCATIONID;
				$this->soapMessage['request']['ipAddress']=betfairConstants::IPADDRESS;
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
