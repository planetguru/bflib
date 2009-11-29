<?php

/* 
* Include the controller class file, which includes
*  __autoload instructions for the rest of the framework
*/

include ('../classes/betfairController.php');

$controller = new betfairController( $_SERVER['REQUEST_URI'] );

$controller->run();

?>
