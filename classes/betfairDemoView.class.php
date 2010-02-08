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
		$displayUnit='';
		$hostname = betfairDemoConstants::HOSTNAME;
		switch( $this->context ){
			case 'getAllEventTypes':
				foreach($this->soapResponse->Result->eventTypeItems->EventType as $eventType){
					$displayUnit.="<li><p><a href='http://".betfairDemoConstants::HOSTNAME."/v1/getEvents/{$eventType->id}'>{$eventType->name}</a></li>";
				}
		 		break;

                        case 'getEvents':
                                /* either this is another list of event nodes, or it is a market summary */
                                if(isset($this->soapResponse->Result->marketItems->MarketSummary)){
					if(is_array($this->soapResponse->Result->marketItems->MarketSummary)){
						foreach($this->soapResponse->Result->marketItems->MarketSummary as $market){
							$displayUnit.="<li><p><a href='http://".betfairDemoConstants::HOSTNAME."/v1/getMarket/{$market->marketId}'>{$market->marketName}</a></p></li>";
						}
					}else{
						$market = $this->soapResponse->Result->marketItems->MarketSummary;
						$displayUnit.="<li><p><a href='http://".betfairDemoConstants::HOSTNAME."/v1/getMarket/{$market->marketId}'>{$market->marketName}</a></p></li>";
					}
                                }if(isset($this->soapResponse->Result->eventItems->BFEvent)){
					/* 
					* if it's an array, there are more than one events, otherwise there is just one 
					* this is somewhat broken in that the datatype switches from an object to an object array depending on 
					* the number of items.  Probably it would be better if BF just served up an array with a
					* single object in it in cases where there is only one eventItem under an eventParent 
					*
					*/
					if(is_array($this->soapResponse->Result->eventItems->BFEvent)){
						foreach($this->soapResponse->Result->eventItems->BFEvent as $event){
							$displayUnit.="<li><p><a href='http://".betfairDemoConstants::HOSTNAME."/v1/getEvents/{$event->eventId}'>{$event->eventName}</a></p></li>";
						}
					}else{
						$event = $this->soapResponse->Result->eventItems->BFEvent;
						$displayUnit.="<li><p><a href='http://".betfairDemoConstants::HOSTNAME."/v1/getEvents/{$event->eventId}'>{$event->eventName}</a></p></li>";
					}
                                }
                                break;


			case 'getAllMarkets':
				$markets = explode(":",$this->soapResponse->Result->marketData);
				array_shift($markets);
				foreach($markets as $market){
					$marketData = explode('~',$market);
					$displayUnit.="<li> {$marketData[1]}
						<a href='http://".betfairDemoConstants::HOSTNAME."/v1/getCompleteMarketPricesCompressed/{$marketData[0]}'>get compressed</a> |
						<a href='http://".betfairDemoConstants::HOSTNAME."/v1/getMarketPriceCompressed/{$marketData[0]}'>get market</a> |
						<a href='http://".betfairDemoConstants::HOSTNAME."/v1/getBFMarketPrices/{$marketData[0]}'>get BF market prices</a> |
						</li>";
				}
				break;

			case 'getInPlayMarkets':
				$markets = explode(":",$this->soapResponse->Result->marketData);
				array_shift($markets);
				foreach($markets as $market){
					$marketData = explode('~',$market);
					$displayUnit.="<li><a href='http://".betfairDemoConstants::HOSTNAME."/v1/getCompleteMarketPricesCompressed/{$marketData[0]}'>{$marketData[1]}</a></li>";
				}
				break;

			case 'getMarket':
				if(betfairConstants::ERROR_OK === $this->soapResponse->Result->header->errorCode){
					$marketDescription = $this->soapResponse->Result->market->marketDescription;
					include('templates/marketData.tpl.php');
					$displayUnit=betfairHelper::returnVar($this->soapResponse);
				}else{
				}
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
				$displayUnit.="<p><a href='http://".betfairDemoConstants::HOSTNAME."/v1/getAllEventTypes/'>get all event types</a></p>";
				$displayUnit.="<p><a href='http://".betfairDemoConstants::HOSTNAME."/v1/getAccountFunds/'>get account funds</a></p>";
				$displayUnit.="<p><a href='http://".betfairDemoConstants::HOSTNAME."/v1/getAllCurrencies/'>get all currencies</a></p>";
				$displayUnit.="<p><a href='http://".betfairDemoConstants::HOSTNAME."/v1/getPaymentCard/'>get payment card</a></p>";
				break;
		}
		include('templates/header.tpl.php');
		include('templates/body.tpl.php');
		$chunk .= <<<EOT
	    </div>
EOT;
return($chunk);
	}
}
?>
