<?php
/**
 * @package Evercate signup
 * @version 1.0.0
 */
/*
Plugin Name: Evercate signup
Plugin URI: https://github.com/Evercate/evercate-signup-wordpress-plugin
Description: A plugin to create signup forms that signs users up to Evercate and assigns them tags as defined for each form
Author: Rickard Liljeberg
Version: 1.0.0
*/

require_once('EvercateSignupOptions.php');

function testfunc($attributes) {
	$myreturnString = "testoutput";
	
	$parsedAttributes = shortcode_atts( array(
		'id' => NULL
	), $attributes );
	
	if($parsedAttributes['id'] === NULL)
	{
		$myreturnString .= " Whopsie";	
	}
	else
	{
		$myreturnString .= " ID: ".$parsedAttributes['id'];
	}
	
	return $myreturnString;
}

add_shortcode( 'testshortcode', 'testfunc' );


