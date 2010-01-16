<?php
/* 
* Include the demo application controller class file, which includes
*  __autoload instructions for the bflib framework
*/

include ('../classes/betfairDemoRequestHandler.php');

$demoController = new betfairDemoRequestHandler( $_SERVER['REQUEST_URI'] );

$demoController->run();

?>
