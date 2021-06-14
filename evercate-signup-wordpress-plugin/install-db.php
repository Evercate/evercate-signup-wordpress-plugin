<?php

global $evercate_signup_db_version;
$evercate_signup_db_version = '1.0';

/*Note: dbDelta allows upgrading a database by simply changnig the sql (and abiding by it's many rules)
However this file is called via register_activation_hook which will not be called when the plugin is upgrades
So if we want a more graceful upgrade of the db when we update the plugin we could create an upgrade_db function and 
connect it to other hook in therein check version via the evercate_signup_db_version option */

function install_db() {
	global $wpdb;
	global $evercate_signup_db_version;
	$charset_collate = $wpdb->get_charset_collate();

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$formTable = "CREATE TABLE " . $wpdb->prefix . "evercate-signup-form (
		id int(11) NOT NULL UNSIGNED AUTO_INCREMENT,
		name VARCHAR(90) NOT NULL,
		created DATETIME NOT NULL DEFAULT (UTC_TIMESTAMP),
		PRIMARY KEY  (id),
		INDEX NAME (name ASC) VISIBLE,
		INDEX CREATED (created ASC) VISIBLE);";

	// $sql = "CREATE TABLE $table_name (
	// 	id mediumint(9) NOT NULL AUTO_INCREMENT,
	// 	created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	// 	name tinytext NOT NULL,
	// 	text text NOT NULL,
	// 	url varchar(55) DEFAULT '' NOT NULL,
	// 	PRIMARY KEY  (id)
	// ) $charset_collate;";

	dbDelta($formTable );

	add_option( 'evercate_signup_db_version', $evercate_signup_db_version );
}

