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
* The betfairDemoRequestHandler class acts as an application controller for the bflibDemo. This class
* translates user actions (URL parameters) into machine interactions with the betfairController within
* the bflib framework.
*
*/
class betfairDemoRequestHandler {
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
		/* determine a 'context' and 'itemId' for this call */
		$this->splitRequestURI( $uri );

		/* override context with a mapping - to translate from URL to WSDL method */
		$this->context = $this->mapContext($this->context);
	}

	/**
	* Instantiate a betfairController object, passing through the mapped context and itemId
	* and then grab the soapresponse after the soap call is made. After that, we have a chance
	* to decide if there is another call required, or if we can just pass the prepared data
	* through to the view
	*
	*/
	public function run(){
		/* we will need the view at the end of this method */
		$this->view = new betfairDemoView();
		$this->bflib = new betfairController();
		$soapResult = array();
	
		$firstSoapResponse = $this->scheduleRequest();	
		$soapResult = $firstSoapResponse;

		/* at this point, I might or might not have enough data to render the view. Test for that */	
		/* currently this is hard-coded to handle merging of market data with its runner names, so won't */
		/* work for any other actions which require more than one call.. some abstraction required */
		$nextContext = $this->getNextRequest();

		if(false !== $nextContext){
			/* we need to make a second call */
			$this->context = $nextContext;
			$this->itemId = $firstSoapResponse->Result->marketDataItems[0]->marketId;
			$secondSoapResponse = $this->scheduleRequest();	
			foreach($firstSoapResponse->allRunnerData as &$selection){
				foreach($secondSoapResponse->Result->market->runners->Runner as &$runner){
					if($runner->selectionId == $selection->selectionId){
						$selection->name=$runner->name;
					}
				}
			}
		}
		/* just hand over to the view class */	
		$this->view->setSoapResponse($soapResult);
 		$this->view->setContext( $this->requestParts[3] );
		$this->html = $this->view->render();
		$this->display();
	}

	public function scheduleRequest(){
		/* set the bflib context as this context */
		$this->bflib->context = $this->context;

		/* pass in the itemId to this bflib */
		$this->bflib->itemId = $this->itemId;

		/* call the bflib->constructRequestData method to set up the soap data */
		$this->bflib->constructRequestData($this->context, $this->itemId);

		/* run the bflib->execute and bflib->prepareResponseData requests and capture the soapResult which comes back */	
		$soapResponse = $this->bflib->run();

		return($soapResponse);
	}

	/*
	* flush the html out to the browser
	*
	*/
	private function display(){
		echo($this->html);
	}	

	/*
	* sometimes a request needs a further soap request for complete data. This method indicates if this applies 
	*
	* @return either boolean false, or string representing next call
	*/
	private function getNextRequest(){
		$return = false;

		if(isset($this->requestParts[3]) && 'getCompleteMarketPricesCompressed' == $this->context){
			$return = 'getMarket';
		}

		return $return;
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
		if( isset($this->requestParts[3]) ){
			/* look at the requestParts[3] value to determine the 'context' */
			$this->context = $this->requestParts[3];
		}

		if( isset($this->requestParts[4]) && true === is_numeric($this->requestParts[4]) ){
			$this->itemId = $this->requestParts[4];
		}else{
			$this->itemId = 0;
		}
	}

	public function mapContext( $action ){
		switch($action){
			case 'getBFMarketPrices':
				$context = 'getCompleteMarketPricesCompressed';
				return $context;
				break;

			default:
				return $action;
				break;
		}
	}
}
?>
