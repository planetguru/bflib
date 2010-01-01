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
class bflibDemoView {
	private $context;
	private $soapResponse;

	/**
	* constructor
	*
	*/
        public function __construct(){ 
	}

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
		$this->soapResponse->Result->header->sessionToken='concealed';
	}

	/**
	* construct markup to return to controller, rendering the soapResponse data
	* 
	*/
        public function render( ){
		/** show a dump of the soapResult object **/
		echo("<textarea rows=10 cols=120>");
			if( false === isset($this->soapResponse) ){
				?>This area will show raw exchange response output with each exchange request<?php
			}else if(true === isset($this->soapResponse->Result->paymentCardItems)){
				?>I am not going to show you this bit :)<?php
			}else{
				betfairHelper::dump($this->soapResponse);
			}
		echo("</textarea>");

		$returnHTML='';

		switch( $this->context ){
			case 'getActiveEventTypes':
			case 'getAllEventTypes':
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
$html="<li> {$marketData[1]} 
<a href='http://".betfairConstants::HOSTNAME."/v1/getCompleteMarketPricesCompressed/{$marketData[0]}'>get complete market prices compressed</a> | 
<a href='http://".betfairConstants::HOSTNAME."/v1/getMarket/{$marketData[0]}'>get market</a> |
<a href='http://".betfairConstants::HOSTNAME."/v1/getBFMarketPrices/{$marketData[0]}'>get BF market prices</a> |
</li>";
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

			case 'getMarket':
				betfairHelper::dump($this->soapResponse);
				break;

			case 'getCompleteMarketPricesCompressed':
				betfairHelper::dump($this->soapResponse);
				break;


			default:
				$returnHTML="<br/><a href='http://".betfairConstants::HOSTNAME."/v1/getActiveEventTypes/'>get active event types</a>";
				$returnHTML.="<br/><a href='http://".betfairConstants::HOSTNAME."/v1/getAllEventTypes/'>get all event types</a>";
				$returnHTML.="<br/><a href='http://".betfairConstants::HOSTNAME."/v1/getAccountFunds/'>get account funds</a>";
				$returnHTML.="<br/><a href='http://".betfairConstants::HOSTNAME."/v1/getAllCurrencies/'>get all currencies</a>";
				$returnHTML.="<br/><a href='http://".betfairConstants::HOSTNAME."/v1/getPaymentCard/'>get payment card</a>";
				break;
		}
		return($returnHTML);
        }
}
?>
