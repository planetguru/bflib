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
* 'View' class with display methods for rendering data collected by the betfairDialogue class
* This code will likely be rewritten for any site/product which implements this library, so that
* data can be rendered appropriately. However, the data flow back to the controller will likely
* remain intact whatever your end implementation. Even if you are just re-encoding to JSON or XML
*
* @author Chris Lacy-Hulbert <chris@spiration.co.uk>
*/
class betfairView {
	private $context;
	private $soapResponse;

	/**
	* constructor
	*
	*/
        public function __construct(){ }

	/**
	* register the current context
	*
	* @param string $val context name
	*/
	public function setContext( $val ){
		$this->context = $val;
	}
	
	/**
	* store the soapResponse data
	* @param array $val
	*/	
	public function setSoapResponse( $val ){
		$this->soapResponse = $val;
	}

	/**
	* construct markup to return to controller, rendering the soapResponse data
	* 
	*/
        public function render( ){
		/** show a dump of the soapResult object **/
		echo("<textarea rows=10 cols=120>");
		betfairHelper::dump($this->soapResponse);
		echo("</textarea>");

		$returnHTML='';

		switch( $this->context ){
			case 'getActiveEventTypes':
				foreach($this->soapResponse->Result->eventTypeItems->EventType as $eventType){	
$html="<li>{$eventType->name} - <a href='http://".betfairConstants::HOSTNAME."/v1/getAllMarkets/{$eventType->id}'> get all markets </a> | <a href='http://".betfairConstants::HOSTNAME."/v1/getInPlayMarkets/{$eventType->id}'> get in-play markets </a></li>";
					$returnHTML.=$html;
				}
				break;

			case 'getAllMarkets':
				$markets = explode(":",$this->soapResponse->Result->marketData);
				array_shift($markets);
				foreach($markets as $market){
					$marketData = explode('~',$market);
$html="<li><a href='http://".betfairConstants::HOSTNAME."/v1/getCompleteMarketPricesCompressed/{$marketData[0]}'>{$marketData[1]}</a></li>";
					$returnHTML.= $html;

				}
				break;

			case 'getInPlayMarkets':
				$markets = explode(":",$this->soapResponse->Result->marketData);
				array_shift($markets);
				foreach($markets as $market){
					$marketData = explode('~',$market);
$html="<li><a href='http://".betfairConstants::HOSTNAME."/v1/getCompleteMarketPricesCompressed/{$marketData[0]}'>{$marketData[1]}</a></li>";
					$returnHTML.= $html;

				}
				break;

			case 'getCompleteMarketPricesCompressed':
				betfairHelper::dump($this->soapResponse);
				break;


			default:
				$returnHTML="<br/><a href='http://".betfairConstants::HOSTNAME."/v1/getActiveEventTypes/'>start here</a>";
				break;
		}
		return($returnHTML);
        }
}
?>
