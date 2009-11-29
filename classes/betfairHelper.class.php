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
}

?>
