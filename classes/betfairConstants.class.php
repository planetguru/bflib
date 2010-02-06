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
        CONST DEBUG_MODE = false;

        /*  not yet implemented, but enable to turn on verbose logging */
        CONST LOGGER_MODE = false;
        CONST CURRENCY_CODE = 'GBP';

	/**
	* Hostname should be changed to match the dns hostname 
	* e.g. www.backingline.com, Note: if you are NOT keeping 'bflib' in your path, or 'www',
	* you will have to decrement the positional values in your controller by one.
	*
	* @todo: create a separate configuration value to represent the base path
	*/
	CONST HOSTNAME = '';

	/* examples */
	/* would require modification to splitRequestURI to reduce array indexes by 1 */
        // CONST HOSTNAME = 'www.backingline.com';  

	/* will require a change to .htaccess if served under somthing other than 'bflib' */
	//CONST HOSTNAME = 'www.backingline.com/bflib';
        //CONST HOSTNAME = 'backingline/bflib';

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

	public function __construct(){}
}

?>
