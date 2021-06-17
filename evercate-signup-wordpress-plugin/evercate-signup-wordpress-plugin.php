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
Text Domain: evercate-signup-wordpress-plugin
Version: 1.0.0
*/

function install_db()
{
	require_once('InstallDb.php');	
}
register_activation_hook( __FILE__, 'install_db' );



add_action('wp_enqueue_scripts', 'callback_for_setting_up_scripts');
function callback_for_setting_up_scripts() {
    wp_register_style( 'evercate.signup', plugin_dir_url( __FILE__ ) . 'evercate-signup.css' );
    wp_enqueue_style( 'evercate.signup' );
}

function add_top_menu()
{
	$evercateLogo = "PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+Cjxzdmcgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDg5IDczIiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zOnNlcmlmPSJodHRwOi8vd3d3LnNlcmlmLmNvbS8iIHN0eWxlPSJmaWxsLXJ1bGU6ZXZlbm9kZDtjbGlwLXJ1bGU6ZXZlbm9kZDtzdHJva2UtbGluZWpvaW46cm91bmQ7c3Ryb2tlLW1pdGVybGltaXQ6MjsiPgogICAgPHBhdGggZD0iTTM5LjMsMzcuM0MzOC45LDQwLjggMzcuMSw0NC4yIDM0LjcsNDYuNUw0Miw1My44QzQyLjksNTMgNDMuNyw1MiA0NC41LDUxLjFDNDEuNSw0Ny4zIDM5LjYsNDIuNCAzOS4zLDM3LjNaIiBzdHlsZT0iZmlsbDpyZ2IoMjQ2LDE3MSwxMDMpO2ZpbGwtcnVsZTpub256ZXJvOyIvPgogICAgPHBhdGggZD0iTTY4LjgsOC40Qzc0LjIsOS41IDc5LDEyLjEgODIuOCwxNS43Qzc4LjgsOC45IDcyLjgsMy40IDY1LjcsMEM2Ny4yLDIuNSA2OC4zLDUuNCA2OC44LDguNFoiIHN0eWxlPSJmaWxsOnJnYigyNDYsMTcxLDEwMyk7ZmlsbC1ydWxlOm5vbnplcm87Ii8+CiAgICA8cGF0aCBkPSJNMjMsMEMxNi40LDMuMiAxMC43LDguMiA2LjgsMTQuM0MxMC41LDExLjIgMTUsOS4xIDIwLDguMkMyMC41LDUuMyAyMS41LDIuNSAyMywwWiIgc3R5bGU9ImZpbGw6cmdiKDI0NiwxNzEsMTAzKTtmaWxsLXJ1bGU6bm9uemVybzsiLz4KICAgIDxwYXRoIGQ9Ik03My45LDQ2LjVMODEuMiw1My44Qzg1LjksNDkuMyA4OC45LDQyLjkgODguOSwzNS45Qzg4LjksMjkuMyA4Ni4yLDIzLjIgODIsMTguN0w3NC42LDI2Qzc3LDI4LjYgNzguNSwzMi4xIDc4LjUsMzUuOEM3OC41LDQwIDc2LjcsNDMuOCA3My45LDQ2LjVaIiBzdHlsZT0iZmlsbDpyZ2IoMjQ2LDE3MSwxMDMpO2ZpbGwtcnVsZTpub256ZXJvOyIvPgogICAgPHBhdGggZD0iTTQ0LjQsNTUuOEM0Mi42LDU4LjEgNDAuNCw1OS40IDM3LjksNjAuOUMzOS4zLDYzLjggNDEuMyw2NyA0NC40LDcwQzQ3LjQsNjcuMSA0OS41LDYzLjkgNTAuOSw2MC45QzQ4LjQsNTkuNCA0Ni4yLDU4LjEgNDQuNCw1NS44WiIgc3R5bGU9ImZpbGw6cmdiKDI0NiwxNzEsMTAzKTtmaWxsLXJ1bGU6bm9uemVybzsiLz4KICAgIDxnPgogICAgICAgIDxwYXRoIGQ9Ik04MiwxOC44TDgyLDE4LjhaIiBzdHlsZT0iZmlsbDpyZ2IoMTAxLDE5NiwyMjApO2ZpbGwtcnVsZTpub256ZXJvOyIvPgogICAgICAgIDxwYXRoIGQ9Ik03Ny4yLDE0LjlDNzcuMiwxNC44IDc3LjIsMTQuOCA3Ny4yLDE0LjlDNzQuNCwxMi45IDY5LjQsMTEgNjQsMTFDNTYsMTEgNDksMTQuOCA0NC40LDIwLjZDMzkuOSwxNC44IDMyLjgsMTEgMjQuOCwxMUMxMS4xLDExIDAsMjIuMSAwLDM1LjlDMCw0OS42IDExLjEsNjAuNyAyNC44LDYwLjdDMzEuNSw2MC43IDM3LjUsNTguMSA0Miw1My44TDM0LjcsNDYuNUMzMi4xLDQ4LjkgMjguNiw1MC40IDI0LjgsNTAuNEMyMS4xLDUwLjQgMTcuNyw0OSAxNS4yLDQ2LjdMMzkuMiwzNS44TDM5LjIsMzUuOUMzOS4yLDQ5LjYgNTAuMyw2MC43IDY0LDYwLjdDNjkuMyw2MC43IDc0LjEsNTkuMiA3Nyw1Ny4xQzc4LjYsNTYuMSA4MCw1NSA4MS4zLDUzLjhMNzQsNDYuNUM3My4zLDQ3LjIgNzIuNSw0Ny44IDcxLjYsNDguM0M2OS40LDQ5LjcgNjYuOCw1MC40IDY0LDUwLjRDNTYsNTAuNCA0OS41LDQzLjkgNDkuNSwzNS45QzQ5LjUsMjcuOSA1NiwyMS40IDY0LDIxLjRDNjYuOCwyMS40IDY5LjUsMjIuMiA3MS43LDIzLjZDNzIuOCwyNC4zIDczLjgsMjUuMSA3NC43LDI2LjFMODIsMTguOEM4MC42LDE3LjMgNzksMTYgNzcuMiwxNC45Wk0yNC44LDIxLjRDMjksMjEuNCAzMi44LDIzLjIgMzUuNSwyNi4xTDEwLjQsMzcuNUMxMC4zLDM3IDEwLjMsMzYuNCAxMC4zLDM1LjlDMTAuMywyNy45IDE2LjgsMjEuNCAyNC44LDIxLjRaIiBzdHlsZT0iZmlsbDpyZ2IoMTAxLDE5NiwyMjApO2ZpbGwtcnVsZTpub256ZXJvOyIvPgogICAgPC9nPgo8L3N2Zz4K";

	add_menu_page(
		'Evercate signup', 
		'Evercate signup', 
		'edit_pages', 
		'evercate-signup', 
		'',
		'data:image/svg+xml;base64,'.$evercateLogo,
		66
	);
}
add_action( 'admin_menu', 'add_top_menu');

//must come after admin_menu action bind
require_once('Forms.php');
require_once('Settings.php');


function evercate_signup_shortcode($attributes) {


$myreturnString=<<<HTML
<form class="ec-signup-form ec-signup-use-styles">
        <div class="ec-signup-form-element">
            <label for="ec-signup-input-1" class="ec-signup-label">
                First Name <span class="ec-signup-required">*</span>
            </label>
            <input type="text" id="ec-signup-input-1" name="First Name" class="ec-signup-text-input" required />
        </div>
        <div class="ec-signup-form-element">
            <label for="ec-signup-input-2" class="ec-signup-label">
                Last Name <span class="ec-signup-required">*</span>
            </label>
            <input type="text" id="ec-signup-input-2" name="Last Name" class="ec-signup-text-input" required />
        </div>
        <div class="ec-signup-form-element">
            <label for="ec-signup-input-3" class="ec-signup-label">
                Email address <span class="ec-signup-required">*</span>
            </label>
            <input type="email" id="ec-signup-input-3" name="Email address" class="ec-signup-text-input" required />
        </div>
        <div class="ec-signup-form-element">
            <label for="ec-signup-input-4" class="ec-signup-label">
                Number
            </label>
            <input type="number" id="ec-signup-input-4" name="Number" class="ec-signup-text-input" />
        </div>
        <div class="ec-signup-form-element">
            <fieldset class="ec-signup-fieldset">
                <legend class="ec-signup-legend">Radio buttons</legend>
                <div>
                    <input type="radio" name="Radio buttons - option" id="ec-signup-radio-1-option-1" value="Radio option 1"
                        checked>
                    <label for="ec-signup-radio-1-option-1" class="ec-signup-label-inline">Radio option 1</label>
                </div>
                <div>
                    <input type="radio" name="Radio buttons - option" id="ec-signup-radio-1-option-2" value="Radio option 2">
                    <label for="ec-signup-radio-1-option-2" class="ec-signup-label-inline">Radio option 2</label>
                </div>
            </fieldset>
        </div>
        <div class="ec-signup-form-element">
            <fieldset class="ec-signup-fieldset">
                <legend class="ec-signup-legend">Checkboxes</legend>
                <div>
                    <input type="checkbox" name="Checkboxes - option" id="ec-signup-checkbox-1-option-1" value="Option 1"
                        checked>
                    <label for="ec-signup-checkbox-1-option-1" class="ec-signup-label-inline">Option 1</label>
                </div>
                <div>
                    <input type="checkbox" name="Checkboxes - option" id="ec-signup-checkbox-1-option-2" value="Option 2">
                    <label for="ec-signup-checkbox-1-option-2" class="ec-signup-label-inline">Option 2</label>
                </div>
                <div>
                    <input type="checkbox" name="Checkboxes - option" id="ec-signup-checkbox-1-option-3" value="Option 3">
                    <label for="ec-signup-checkbox-1-option-3" class="ec-signup-label-inline">Option 3</label>
                </div>
            </fieldset>
        </div>
        <div class="ec-signup-form-element">
            
                <label for="ec-signup-input-5" class="ec-signup-label">Select</label>
                <select id="ec-signup-input-5" class="ec-signup-select">

                    <option value="Select option 1">Select option 1</option>
                    <option value="Select option 2">Select option 2</option>
                    <option value="Select option 3">Select option 3</option>

                </select>
            
        </div>
        <div class="ec-signup-form-element">
            <button type="submit" class="ec-signup-button">Skicka</button>
        </div>
    </form>


    <div class="ec-signup-message-container">
        <h3>Message heading</h3>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus congue luctus libero consequat facilisis. </p>
    </div>
HTML;
	
	return $myreturnString;
}

add_shortcode( 'evercate-signup', 'evercate_signup_shortcode' );


