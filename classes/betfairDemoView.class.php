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
                $returnHTML = '';
		$chunk = <<<EOT
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 			"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
   <title>BFlib - Betfair Library - Demo Application</title>
   <link rel="stylesheet" href="http://yui.yahooapis.com/2.7.0/build/reset-fonts-grids/reset-fonts-grids.css" type="text/css">
<link rel="stylesheet" type="text/css" href="http://
EOT;
                $returnHTML.=$chunk;
                $returnHTML.=betfairConstants::HOSTNAME;
                $chunk = <<<EOT
/css/demo.css">
</head>
<body>
<div id="doc3" class="yui-t6">
   <div id="hd" role="banner"><h1>Bflib - Betfair Library Demo Application</h1></div>
   <div id="bd" role="main">
	<div id="yui-main">
	<div class="yui-b"><div class="yui-gd">
    <div class="yui-u first">
EOT;
		$returnHTML.=$chunk;
                if( false === isset($this->soapResponse) ){
                        $returnHTML.= 'This area will show raw exchange response output with each exchange request';
                }else if(true === isset($this->soapResponse->Result->paymentCardItems)){
                        $returnHTML.='I am not going to show you this bit :)';
                }else{
                        $returnHTML.=betfairHelper::returnVar($this->soapResponse);
                }
                $chunk = <<<EOT
	    </div>
    <div class="yui-u">
EOT;
		$returnHTML.=$chunk;
		switch( $this->context ){
			case 'getActiveEventTypes':
			case 'getAllEventTypes':
				foreach($this->soapResponse->Result->eventTypeItems->EventType as $eventType){
					$chunk="<li><p>{$eventType->name} - <a href='http://".betfairConstants::HOSTNAME."/v1/getAllMarkets/{$eventType->id}'> get all markets </a> | <a href='http://".betfairConstants::HOSTNAME."/v1/getInPlayMarkets/{$eventType->id}'> get in-play markets </a></p></li>";
					$returnHTML.=$chunk;
				}
	 		break;

			case 'getAllMarkets':
				$markets = explode(":",$this->soapResponse->Result->marketData);
				array_shift($markets);
				foreach($markets as $market){
					$marketData = explode('~',$market);
					$html="<li> {$marketData[1]}
						<a href='http://".betfairConstants::HOSTNAME."/v1/getCompleteMarketPricesCompressed/{$marketData[0]}'>get prices compressed</a> |
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
					$h="<li><a href='http://".betfairConstants::HOSTNAME."/v1/getCompleteMarketPricesCompressed/{$marketData[0]}'>{$marketData[1]}</a></li>";
					$returnHTML.= $h;
				}
				break;

			case 'getMarket':
	 			$returnHTML.=betfairHelper::returnVar($this->soapResponse);
				break;

			case 'getCompleteMarketPricesCompressed':
				$returnHTML.=betfairHelper::returnVar($this->soapResponse);
				break;

			case 'getBFMarketPrices':
				$returnHTML.='<table>';
				foreach($this->soapResponse->allRunnerData as &$runner){
					$returnHTML.='<tr>';
						foreach($runner->prices as $priceDetails){
						$chunk='<p>odds: '.$priceDetails->odds.'- back: '.$priceDetails->backAmountAvailable.'- lay: '.$priceDetails->layAmountAvailable;
						$returnHTML.=$chunk.'</p>';
					}
					$returnHTML.='</tr>';
				}
				$returnHTML.='</table>';
				break;	
								
			default:
				$returnHTML.="<br/><a href='http://".betfairConstants::HOSTNAME."/v1/getActiveEventTypes/'>get active event types</a>";
				$returnHTML.="<br/><a href='http://".betfairConstants::HOSTNAME."/v1/getAllEventTypes/'>get all event types</a>";
				$returnHTML.="<br/><a href='http://".betfairConstants::HOSTNAME."/v1/getAccountFunds/'>get account funds</a>";
				$returnHTML.="<br/><a href='http://".betfairConstants::HOSTNAME."/v1/getAllCurrencies/'>get all currencies</a>";
				$returnHTML.="<br/><a href='http://".betfairConstants::HOSTNAME."/v1/getPaymentCard/'>get payment card</a>";
				break;
		}
		$chunk = <<<EOT
	    </div>
</div>
</div>
	</div>
	<div class="yui-b">
		<div class="counit">
<a href="http://www.anrdoezrs.net/gj117vpyvpxCGKLLEHJCEDJMDJFL" target="_top" onmouseover="window.status='http://www.totesport.com/asset_tracker?action=go_asset&new=1&aff_id=115&asset_id=118';return true;" onmouseout="window.status=' ';return true;">
<img src="http://www.awltovhc.com/46108m-3sywHLPQQJMOHJIORIOKQ" alt="Bet now with totesport" border="0"/></a>
		</div>
		<div class="counit">
<a href="http://www.dpbolvw.net/hj115hz74z6MQUVVORTMONPVOTOW" target="_top" onmouseover="window.status='http://www.betfair.com';return true;" onmouseout="window.status=' ';return true;">
<img src="http://www.awltovhc.com/qn72snrflj48CDD69B4657D6B6E" alt="130x100_Seasonal" border="0"/></a>
		</div>
		<div class="counit">
		<a href="http://www.bfbotmanager.com/cgi-bin/affiliate.pl?affiliate_id=30" target="_blank"><img style="border: 0px;" src="http://www.bfbotmanager.com/adverts/bf_bot_140x91.gif"></a>
		</div>
	</div>
	</div>
   <div id="ft" role="contentinfo"><p>Back to Backingline, Befair.com, BDP</p></div>
</div>
</body>
</html>
EOT;
		$returnHTML.=$chunk;
		return($returnHTML);
	}
}
?>
