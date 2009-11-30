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
* Define autoload classpath and require it in.
*
* @param $class The class to be loaded
*
*/
function __autoload( $class ){
	$classpath = '../classes/'.$class.'.class.php';
	if (file_exists( $classpath )){
		require_once($classpath);
	}
}

/**
* The betfairController class loosely acts as an application controller in MVC-speak. This class handles
* interaction between the betfairDialogue and the betfairView. The controller is also responsible for
* discerning context from the parent URI and eventually serving the rendered view output to the user
*
*/
class betfairController {
	public $requestURI;
	public $context = '';
	public $data = array();
	public $requestParts = array();
	private $itemId;
	private $html;

	/**
	* construct controller object
	*
	*/
        public function __construct( $uri ){ 
		$this->splitRequestURI( $uri );
	}

	/**
	* Instantiate a betfairdialogue object, request function lists from the service WSDLs,
	* log in and pass a request through the client, according to the current request 'context'
	* Then hand over to the view class to render any output/soapresponse
	*
	*/
	public function run(){
		/* we will need the view at the end of this method */
		$this->view = new betfairView();

		/* if there is no context, there is nothing to run */
		if( false === empty( $this->context )){
			$this->dialogue = betfairDialogue::getInstance();
			$this->dialogue->connect();	
			$this->dialogue->getFunctionsFromWSDL();

			/** first login - currently on every call with forced 'login' context **/
			$loginresult = $this->login();
			$reqdata = $this->constructRequestData($this->context, $this->itemId, $this->requestURI);

			/** then call the required context if set **/ 
			if(!empty($this->context)){
				$this->data = $reqdata;
				$this->dialogue->setContext($this->context);
				$this->dialogue->setData($this->data);
				$soapResult = $this->dialogue->execute();
				$soapResult = $this->dialogue->prepareData($soapResult);
			}
			$this->view->setContext( $this->dialogue->getContext() );
			$this->view->setSoapResponse($soapResult);
		}
		$this->html = $this->view->render();
		$this->display();
	}

	/*
	* flush the html out to the browser
	*
	*/
	private function display(){
		echo($this->html);
	}	

	/*
	* Set up a 'login' request to be passed through the current dialogue
	* 
	* @return soapResult
	*/
	public function login(){
		$this->dialogue->setContext('login');
		$this->dialogue->setData($this->constructRequestData('login', 0, $this->requestURI));  
		$soapResult = $this->dialogue->execute();
		return($soapResult);
	}

	/** 
	* Break the URI into constituent parts and set the current context for this bf dialogue
	* also set the requestParts into a class array variable

	* @param $uri the URI of the request being handled
	*/
	public function splitRequestURI( $uri ){
		$this->requestURI = $uri;

		/**  URI looks like:  /v1/GetEvents/13/'; */
		$this->requestParts = explode('/',$uri);
		//betfairHelper::dump($this->requestParts);
		if( isset($this->requestParts[3]) ){
			$this->context = $this->requestParts[3];
		}

		if( isset($this->requestParts[4]) && true === is_numeric($this->requestParts[4]) ){
			$this->itemId = $this->requestParts[4];
		}else{
			$this->itemId = 0;
		}
	}

	/** 
	* Based on the context of this request and the 'id' pulled from the request URI,
	* Set up some request parameters to be passed in the soap message by the dialogue object
	*
	* @param $context the context, or 'verb' of the current request
	* @param $id the id on which the method will be run. Usually an integer, but could be an array of ints
	* @param $url passed in for convenience in cases where the verb and target id are insufficient
	*
	* @return the soapMessage array with fully constructed request and session data
	*/
	public function constructRequestData($context, $id, $uri){
		$soapMessage = array();
		$soapMessage['request']=array();

		/* text the context and set parameters as necessary */
		switch($context){
			case 'login':
				$soapMessage['request']['username']=betfairConstants::USERNAME;
				$soapMessage['request']['password']=betfairConstants::PASSWORD;
				$soapMessage['request']['productId']=betfairConstants::PRODUCTID;
				$soapMessage['request']['vendorSoftwareId']=betfairConstants::VENDORID;
				$soapMessage['request']['locationId']=betfairConstants::LOCATIONID;
				$soapMessage['request']['ipAddress']=betfairConstants::IPADDRESS;
				break;

			case 'getAllMarkets':
				$soapMessage['request']['eventTypeIds'][] = $id;
				break;
		
			case 'getCompleteMarketPricesCompressed':
				$soapMessage['request']['marketId'] = $id;
				$soapMessage['request']['currencyCode'] = betfairConstants::CURRENCY_CODE;
				break;

			case 'GetEvents':	
				//$soapMessage['request']['eventParentId']=$list[3];
				$soapMessage['request']['eventParentId']=$id;
				break;

			case 'GetEvent':
				//$soapMessage['request']['eventParentId']=$list[3];
				$soapMessage['request']['eventParentId']=$id;
				break;

			case 'GetMarketPrices';
				$soapMessage['request']['marketId'] = $id;
				$soapMessage['request']['currencyCode'] = 'GBP';
				break;
	
		}
	
		$soapMessage['request']['header']=array('clientStamp' => 0, 'sessionToken' => $this->dialogue->getSessionToken() );
		return($soapMessage);
	}
}
?>
