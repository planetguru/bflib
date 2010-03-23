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

	/* HOSTNAME has been moved into the application config */

	/**
	* bdp developer credentials. You should change the USERNAME and
	* PASSWORD to match your own username and password on betfair.
	* Remember that your account will need to be active, with recent transactions and
	* cleared funds in order for the SOAP APIs to allow you to authenticate
	*/
	const USERNAME     = '';
	const PASSWORD     = '';
	const PRODUCTID    = 82;   // 82 for non-vendor, else 0.
	const VENDORID     = 0;  // v1242
	const LOCATIONID   = 0;
	const IPADDRESS    = 0;

	/**
	* Error codes
	*
	*/
	const ERROR_OK = 'OK';
	const ERROR_EVENT_CLOSED = 'EVENT_CLOSED';

	/**
	* Bet related constants
	* 
	*/
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
