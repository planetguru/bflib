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
* 'View' class with display methods for rendering data received by the bflib Demo application controller
* This code will likely be rewritten for any site/product which implements this library, so that
* data can be rendered appropriately. However, the data flow back to the controller will likely
* remain intact whatever your end implementation. 
*
* @author Chris Lacy-Hulbert <chris@spiration.co.uk>
*/
class betfairDemoView {
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
                $this->soapResponse->Result->header->sessionToken='concealed';
        }

        /**
        * construct markup to return to controller, rendering the soapResponse data
        * 
        */
        public function render( ){
		$chunk = '';
		include('templates/header.tpl.php');
		switch( $this->context ){
			case 'getActiveEventTypes':
			case 'getAllEventTypes':
				foreach($this->soapResponse->Result->eventTypeItems->EventType as $eventType){
					$displayUnit.="<li><p>{$eventType->name} - <a href='http://".betfairConstants::HOSTNAME."/v1/getAllMarkets/{$eventType->id}'> get all markets </a> | <a href='http://".betfairConstants::HOSTNAME."/v1/getInPlayMarkets/{$eventType->id}'> get in-play markets </a></p></li>";
				}
	 		break;

			case 'getAllMarkets':
				$markets = explode(":",$this->soapResponse->Result->marketData);
				array_shift($markets);
				foreach($markets as $market){
					$marketData = explode('~',$market);
					$displayUnit.="<li> {$marketData[1]}
						<a href='http://".betfairConstants::HOSTNAME."/v1/getCompleteMarketPricesCompressed/{$marketData[0]}'>get prices compressed</a> |
						<a href='http://".betfairConstants::HOSTNAME."/v1/getMarket/{$marketData[0]}'>get market</a> |
						<a href='http://".betfairConstants::HOSTNAME."/v1/getBFMarketPrices/{$marketData[0]}'>get BF market prices</a> |
						</li>";
				}
				break;

			case 'getInPlayMarkets':
				$markets = explode(":",$this->soapResponse->Result->marketData);
				array_shift($markets);
				foreach($markets as $market){
					$marketData = explode('~',$market);
					$displayUnit.="<li><a href='http://".betfairConstants::HOSTNAME."/v1/getCompleteMarketPricesCompressed/{$marketData[0]}'>{$marketData[1]}</a></li>";
				}
				break;

			case 'getMarket':
	 			$displayUnit=betfairHelper::returnVar($this->soapResponse);
				break;

			case 'getCompleteMarketPricesCompressed':
				$displayUnit=betfairHelper::returnVar($this->soapResponse);
				break;

			case 'getBFMarketPrices':
				$displayUnit='<table>';
				foreach($this->soapResponse->allRunnerData as &$runner){
					$displayUnit.='<tr>';
						foreach($runner->prices as $priceDetails){
						$displayUnit.='<p>odds:'.$priceDetails->odds.'- back: '.$priceDetails->backAmountAvailable.'- lay: '.$priceDetails->layAmountAvailable;
						$displayUnit.='</p>';
					}
					$displayUnit.='</tr>';
				}
				$displayUnit.='</table>';
				break;	
								
			default:
				$displayUnit.="<p><a href='http://".betfairConstants::HOSTNAME."/v1/getActiveEventTypes/'>get active event types</a></p>";
				$displayUnit.="<p><a href='http://".betfairConstants::HOSTNAME."/v1/getAllEventTypes/'>get all event types</a></p>";
				$displayUnit.="<p><a href='http://".betfairConstants::HOSTNAME."/v1/getAccountFunds/'>get account funds</a></p>";
				$displayUnit.="<p><a href='http://".betfairConstants::HOSTNAME."/v1/getAllCurrencies/'>get all currencies</a></p>";
				$displayUnit.="<p><a href='http://".betfairConstants::HOSTNAME."/v1/getPaymentCard/'>get payment card</a></p>";
				break;
		}
		include('templates/body.tpl.php');
		$chunk .= <<<EOT
	    </div>
EOT;
return($chunk);
	}
}
?>
