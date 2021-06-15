<?php


/*Note: dbDelta allows upgrading a database by simply changnig the sql (and abiding by it's many rules)
However this file is called via register_activation_hook which will not be called when the plugin is upgrades
So if we want a more graceful upgrade of the db when we update the plugin we could create an upgrade_db function and 
connect it to other hook in therein check version via the evercate_signup_db_version option */

function install_initial_db() {

	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$queries = array();

	$queries[] = "CREATE TABLE " . $wpdb->prefix . "evercate_signup_form (
		id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		name VARCHAR(90) NOT NULL,
		created DATETIME NOT NULL DEFAULT (UTC_TIMESTAMP),
		PRIMARY KEY  (id),
		INDEX NAME (name ASC) VISIBLE,
		INDEX CREATED (created ASC) VISIBLE
	)".$charset_collate.";";


	$result = dbDelta($queries);

	//Note, this is just so that we in the future can write update code if we need to update database
	add_option( 'evercate_signup_db_version', '1.0' );

	//wp_die($result);
}

install_initial_db();