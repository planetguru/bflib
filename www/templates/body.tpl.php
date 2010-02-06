<?php
if( false === isset($this->soapResponse) ){
	$chunk.= 'This area will show raw exchange response output with each exchange request';
}else if(true === isset($this->soapResponse->Result->paymentCardItems)){
	$chunk.='I am not going to show you this bit. ';
}else{
	$chunk.=betfairHelper::returnVar($this->soapResponse);
}
$chunk.=<<<EOT
            </div>
    <div class="yui-u">
	    </div>
    <div class="yui-u">
EOT;
	if(isset($displayUnit)){
		$chunk.=$displayUnit;
	}
$chunk.=<<<EOT
    </div>
</div>
</div>

	</div>
	<div class="yui-b">
		<!--div class="counit">
<a href="http://www.anrdoezrs.net/gj117vpyvpxCGKLLEHJCEDJMDJFL" target="_top" onmouseover="window.status='http://www.totesport.com/asset_tracker?action=go_asset&new=1&aff_id=115&asset_id=118';return true;" onmouseout="window.status=' ';return true;">
<img src="http://www.awltovhc.com/46108m-3sywHLPQQJMOHJIORIOKQ" alt="Bet now with totesport" border="0"/></a>
		</div-->
		<div class="counit">
<a href="http://www.dpbolvw.net/hj115hz74z6MQUVVORTMONPVOTOW" target="_top" onmouseover="window.status='http://www.betfair.com';return true;" onmouseout="window.status=' ';return true;">
<img src="http://www.awltovhc.com/qn72snrflj48CDD69B4657D6B6E" alt="130x100_Seasonal" border="0"/></a>
		</div>
		<div class="counit">
		<a href="http://www.bfbotmanager.com/cgi-bin/affiliate.pl?affiliate_id=30" target="_blank"><img style="border: 0px;" src="http://www.bfbotmanager.com/adverts/bf_bot_140x91.gif"></a>
		</div>

	</div>
	</div><!-- end of main section -->
EOT;
?>
