<?php
/*
Plugin Name: Mr. Beta
Plugin URI: http://juliabalfour.com
Description: Julia Balfour, LLC's beta testing / bug reporter plugin
Version: 1.0
Author: Joseph Thibeault, Julia Balfour LLC
Author URI: http://juliabalfour.com
License: A "Slug" license name e.g. GPL2
*/

register_activation_hook(__FILE__, 'jb_beta_activate');
register_deactivation_hook(__FILE__, "jb_beta_deactivate");
register_uninstall_hook(__FILE__, "jb_beta_uninstall");


function jb_beta_deactivate(){
	
	//delete_option('simple-backup-file-transfer');
	//delete_option('simple-backup-background-processing');	
	
}


function jb_beta_uninstall(){
	
	//delete_option('simple-backup-settings');
	//delete_option('simple-backup-file-transfer');
	//delete_option('simple-backup-background-processing');	
	
}


function jb_beta_activate() {

	// display error message to users
	if ($_GET['action'] == 'error_scrape') {                                                                                                   
	    die("Sorry, JB Beta Plugin requires PHP 5.2 or higher. Please deactivate JB Beta Plugin.");                                 
	}
	
	if ( version_compare( phpversion(), '5.2', '<' ) ) {
		trigger_error("", E_USER_ERROR);
	}
	
}


// require simple backup Plugin if PHP 5.2 installed
if ( version_compare( phpversion(), '5.2', '>=') ) {

	define('JB_BETA_LOADER', __FILE__);
	
	//require_once(dirname(__FILE__) . '/jb-beta-db.php');
	
	require_once(dirname(__FILE__) . '/jb-beta-plugin.php');
	
	//$JB_List_Table = new JB_BETA_LIST_TABLE();
	
	$jb_beta = new JB_Beta_Plugin();
	
	
	

}

?>