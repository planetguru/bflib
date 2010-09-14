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
* Helper class - currently just used for debugging to stdout
* should have a proper ::logger method for live debugging
* methods called statically.
*
*/
class betfairHelper {
	/**
	* Dump data to stdout
	*
	* @param $val data to be dumped
	*/
	public function dump($val){
		echo('<pre>');
		var_dump($val);
		echo('</pre>');
	}

	/**
	* Dump data to stdout
	*
	* @param $val data to be dumped
	*/
	public function returnVar($val){
		return(print_r($val,true));
	}

	/**
	* Dump data to stdout and stop execution
	*
	* @param $val data to be dumped
	*/
	public function dumpAndExit($val){
		echo('<pre>');
		var_dump($val);
		echo('</pre>');
		exit();
	}

	/*
	* Based on the increments defined in the Betfair odds ladder,
	* indicate what the next incremental price would be from the price provided
	*
	* @param val - base price to test
	* @return price - next increment up from val
	*/
		public function getNextIncrement($val){
		if($val <1 ){
			return 0;
		}else if($val >=1 && $val<2){
			return 0.01;
		}else if($val >=2 && $val<3){
			return 0.02;
		}else if($val >=3 && $val<4){
			return 0.05;
		}else if($val >=4 && $val<6){
			return 0.1;
		}else if($val >=6 && $val<10){
			return 0.2;
		}else if($val >=10 && $val<20){
			return 0.5;
		}else if($val >=20 && $val<30){
			return 1;
		}else if($val >=30 && $val<50){
			return 2;
		}else if($val >=50 && $val<100){
			return 0.5;
		}else if($val >=100 && $val<1000){
			return 10;
		}else if($val >=1000){
			return 0;
		}
	}

	/*
	* Based on the increments defined in the Betfair odds ladder,
	* indicate what the next decremented price would be from the price provided
	*
	* @param val - base price to test
	* @return price - next decrement down from val
	*/
	public function getNextDecrement($val){
		if($val <1 ){
			return 0;
		}else if($val >1 && $val<=2){
			return 0.01;
		}else if($val >2 && $val<=3){
			return 0.02;
		}else if($val >3 && $val<=4){
			return 0.05;
		}else if($val >4 && $val<=6){
			return 0.1;
		}else if($val >6 && $val<=10){
			return 0.2;
		}else if($val >10 && $val<=20){
			return 0.5;
		}else if($val >20 && $val<=30){
			return 1;
		}else if($val >30 && $val<=50){
			return 2;
		}else if($val >50 && $val<=100){
			return 0.5;
		}else if($val >100 && $val<=1000){
			return 10;
		}else if($val >1000){
			return 0;
		}
	}

	/*
	* Remove BSP prices from pre-prepared array of runner data as would be returned by 
	* the extractVolumes method
	* 
	* @param $runnerData
	* @return $runnerData
	*/
        public function removeBSPPrices( $runnerData ){
                foreach($runnerData as $runner){
                        $runner->newPrices = array();
                        foreach($runner->prices as $price){
                                unset($price->BSPBackAvailableAmount);
                                unset($price->BSPLayAvailableAmount);
                                unset($price->layAmountAvailable);
                                $runner->newPrices[]=$price;
                        }
                        $runner->prices = $runner->newPrices;
                }
                return($runnerData);
        }


	/*
	* Extract the top back prices and volumes for each runner on a market
	*
	* @param $runnerData as parsed from getCompleteMarketPricesCompressed, with name data added
	* @return object $result
	*/
        public function extractVolumes( $runnerData ){
                $runnerId = 0;

                foreach($runnerData as $runnerItem){
                        /* */
                        $results['priceArray'][$runnerId]['name'] = (string)$runnerItem->name;
                        $results['priceArray'][$runnerId]['selectionId'] = $runnerItem->selectionId;
                        $results['priceArray'][$runnerId]['amountMatched'] = (string)($runnerItem->amountMatched);
                        $savedPrice = 0;
                        $savedAmount = 0;
                        foreach($runnerItem->prices as $price){
                                if(true === ($price->backAmountAvailable > 0)){
                                        $price->odds = (string)$price->odds;
                                        if($price->odds > $savedPrice){
                                                $savedPrice = $price->odds;
                                                $savedAmount = $price->backAmountAvailable;
                                        }
                                }
                        }

                        $results['priceArray'][$runnerId]['topBack']=sprintf("%.2f",$savedPrice);
                        $results['priceArray'][$runnerId]['topBackVol']=sprintf("%.2f",$savedAmount);
                        $runnerId++;
                }

                $result->resultSet = $results;

                return($result);
        }
}

?>
