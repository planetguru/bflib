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
* Constants used for API6 authentication
*
* @author Chris Lacy-Hulbert <chris@spiration.co.uk>
*/
class vendorConstants {

	/**
	* Change the USERNAME and PASSWORD to match your own username and password on betfair.
	* Remember that your account will need to be active, with recent transactions and
	* cleared funds in order for the SOAP APIs to allow you to authenticate
	*
	*/
	const USERNAME     = '';
	const PASSWORD     = '';
	const PRODUCTID    = 82;
	const VENDORID     = 0;
	const LOCATIONID   = 0;
	const IPADDRESS    = 0;

	/**
	* some settings which are more specific to the vendor/implementation than they are to the bflib core
	*
	*/
	const LOGFILE		='/tmp/betfair-api-log';
	const CACHEFILE		='/tmp/bflib-0001.cache';

	public function __construct(){}
}

?>
