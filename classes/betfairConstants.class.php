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
* Constants used around the library
*
* @author Chris Lacy-Hulbert <chris@spiration.co.uk>
*/
class betfairConstants {
	/**
	* URLs of Betfair's soap exchange WSDL documents
	*
	*/
        CONST GLOBALSERVICE = 'https://api.betfair.com/global/v3/BFGlobalService.wsdl';
        CONST EXCHANGESERVICE = 'https://api.betfair.com/exchange/v5/BFExchangeService.wsdl';

        /* enable debug mode for full dumps of soap error responses. Use with extreme caution */
        /* as this can result in exposing exchange login credentials */
        CONST DEBUG_MODE = true;

        /*  not yet implemented, but enable to turn on verbose logging */
        CONST LOGGER_MODE = false;
        CONST CURRENCY_CODE = 'GBP';

	/**
	* Error codes
	*
	*/
	const ERROR_OK = 'OK';

	/*
	* non-service errors - usually API related
	*/
	const ERROR_INTERNAL_ERROR 			= 'INTERNAL_ERROR';
	const ERROR_EXCEEDED_THROTTLE 			= 'EXCEEDED_THROTTLE';
	const ERROR_USER_NOT_SUBSCRIBED_TO_PRODUCT 	= 'USER_NOT_SUBSCRIBED_TO_PRODUCT';
	const ERROR_SUBSCRIPTION_INACTIVE_OR_SUSPENDED 	= 'SUBSCRIPTION_INACTIVE_OR_SUSPENDED';
	const ERROR_VENDOR_SOFTWARE_INACTIVE 		= 'VENDOR_SOFTWARE_INACTIVE';
	const ERROR_VENDOR_SOFTWARE_INVALID 		= 'VENDOR_SOFTWARE_INVALID';
	const ERROR_SERVICE_NOT_AVAILABLE_IN_PRODUCT 	= 'SERVICE_NOT_AVAILABLE_IN_PRODUCT';
	const ERROR_NO_SESSION 				= 'NO_SESSION';
	const ERROR_TOO_MANY_REQUESTS 			= 'TOO_MANY_REQUESTS';
	const ERROR_PRODUCT_REQUIRES_FUNDED_ACCOUNT 	= 'PRODUCT_REQUIRES_FUNDED_ACCOUNT';
	const ERROR_SERVICE_NOT_AVAILABLE_FOR_LOGIN_STATUS = 'SERVICE_NOT_AVAILABLE_FOR_LOGIN_STATUS';
	

	/*
	* service errors correspond to method-level issues eg * missing required data, or parameters, or 
	* event/market in a bad state
	*/
	const ERROR_EVENT_CLOSED = 'EVENT_CLOSED';
	const ERROR_EVENT_SUSPENDED = 'EVENT_SUSPENDED';
	const ERROR_EVENT_INACTIVE = 'EVENT_INACTIVE';


	/**
	* Bet related constants
	*/
	const MINIMUM_BET = 2;
	
	const BACK_BET_TYPE = 'B';
	const LAY_BET_TYPE = 'L';

	const BET_CATEGORY_TYPE_NONE = 'NONE';
	const BET_CATEGORY_TYPE_NORMAL = 'E';
	const BET_CATEGORY_TYPE_MARKETONCLOSE = 'M';
	const BET_CATEGORY_TYPE_LIMITONCLOSE = 'L';

	const BET_PERSISTENCE_NORMAL = 'NONE';
	const BET_PERSISTENCE_SP = 'SP';
	const BET_PERSISTENCE_IP = 'IP';

	public function __construct(){}
}

?>
