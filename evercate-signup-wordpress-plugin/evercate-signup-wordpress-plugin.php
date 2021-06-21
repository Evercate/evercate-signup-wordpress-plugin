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


require_once('Repository.php');	
require_once('EvercateApiClient.php');	
require_once('Model/EvercateUser.php');	

function install_db()
{
	require_once('InstallDb.php');	
}
register_activation_hook( __FILE__, 'install_db' );



add_action('wp_enqueue_scripts', 'callback_for_setting_up_scripts');
function callback_for_setting_up_scripts() {
    wp_register_style( 'evercate.signup', plugin_dir_url( __FILE__ ) . 'evercate-signup.css' );
    wp_enqueue_style( 'evercate.signup' );

	wp_enqueue_script( 
		'form-js',                            // Handle
		plugins_url( '/Form.js', __FILE__ ),  // Path to file
		array( 'jquery' ),                        // Dependancies
		'1.0.0'                                   // Version
	);

	wp_add_inline_script( 'form-js', 
	'const constants = ' . json_encode( 
								array(
									'ajaxUrl' => admin_url( 'admin-ajax.php' ),
									) ),
	'before' );
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
require_once('Signups.php');
require_once('Settings.php');

function evercate_signup_shortcode($attributes) {

	$options = get_option("evercate-signup_options");

	if(!isset($attributes["id"]) || !is_numeric($attributes["id"]))
	{
		wp_die("Invalid id")		;
	}

	$formId = $attributes["id"];

	
	$repository = new Repository();

	$form = $repository->getForm($formId);

	if($form === NULL)
	{
		$message = "Form does not exist, id: ".$formId;
		send_error_mail($message);
		return "<div></div>";
	}

	$apiKey = $options["evercate_api_key"];
	$apiClient = new EvercateApiClient($apiKey);

$returnString=<<<HTMLBASE
<form class="ec-signup-form ec-signup-use-styles" id="evercate-signup-$form->Id" data-form-id="$form->Id" >
		<input type="hidden" name="formId" value="$form->Id" />
		<input type="hidden" name="action" value="evercate-signup-submit" />
        <div class="ec-signup-form-element">
            <label for="evercate-signup-firstname" class="ec-signup-label">
                 $form->FirstNameLabel <span class="ec-signup-required">*</span>
            </label>
            <input type="text" id="evercate-signup-firstname" name="First Name" class="ec-signup-text-input" required />
        </div>
        <div class="ec-signup-form-element">
            <label for="evercate-signup-lastname" class="ec-signup-label">
			$form->LastNameLabel <span class="ec-signup-required">*</span>
            </label>
            <input type="text" id="evercate-signup-lastname" name="Last Name" class="ec-signup-text-input" required />
        </div>
        <div class="ec-signup-form-element">
            <label for="evercate-signup-username" class="ec-signup-label">
			$form->UsernameLabel <span class="ec-signup-required">*</span>
            </label>
            <input type="email" id="evercate-signup-username" name="Email address" class="ec-signup-text-input" required />
        </div>
HTMLBASE;

	if(isset($form->TagTypes) && is_array($form->TagTypes))
	{
		$userGroup = $apiClient->GetUserGroup($options["evercate_group_id"]);

		foreach($form->TagTypes as $tagTypeId => $tagTypeLabel)
		{
			$fullTagType = NULL;
			foreach($userGroup->EvercateTagTypes as $evercateTagType)
			{
				if($evercateTagType->Id === $tagTypeId)
				{
					$fullTagType = $evercateTagType;
					break;
				}
			}

			//The tag type we have saved on this form was not found in the data from the api
			if($fullTagType === NULL)
			{
				$message = "A form had the selecteable tag type ".$tagTypeLabel." (".$tagTypeId."), however no tagtype with this id was found in Evercate.";
				send_error_mail($message);
				//We simply skip this tag type
				continue;
			}

			$returnString .= '<div class="ec-signup-form-element">';
			$returnString .= '<label for="ec-signup-input-'.$tagTypeId.'" class="ec-signup-label">'.$tagTypeLabel.'</label>';
			$returnString .= '<select id="ec-signup-input-'.$tagTypeId.'" name="tagType-'.$tagTypeId.'" class="ec-signup-select">';

			//Consider having empty option here but then language will be an issue on the text of the empty option

			foreach($fullTagType->EvercateTags as $tag)
			{
				$returnString .= '<option value="'.$tag->Id.'">'.$tag->Name.'</option>';
			}
			$returnString .= '</select>';
			$returnString .= '</div>';
		
	
		}
		

	}
	
	$returnString .= '<div class="ec-signup-form-element">';
	$returnString .= '<button type="submit" class="ec-signup-button" id="submit-button-'.$form->Id.'">'.$options["send_button_label"].'</button>';
	$returnString .= '</div>';
	$returnString .= '</form>';

    $returnString .= '<div class="ec-signup-message-container" id="evercate-signup-done-'.$form->Id.'" style="display:none">';
	$returnString .= '<h3>'.$options["success_title"].'</h3>';
	$returnString .= '<p>'.$options["success_message"].'</p>';
    $returnString .= '</div>';

	$returnString .= '<div class="ec-signup-message-container" id="evercate-signup-error-'.$form->Id.'" style="display:none">';
	$returnString .= '<h3>'.$options["error_title"].'</h3>';
	$returnString .= '<p>'.$options["error_message"].'</p>';
    $returnString .= '</div>';
	
	return $returnString;
}

add_shortcode( 'evercate-signup', 'evercate_signup_shortcode' );







function signup_submit_handle()
{
	$formId = $_POST["formId"];

	$repository = new Repository();

	$form = $repository->getForm($formId);

	$options = get_option("evercate-signup_options");
	$selectedGroupId = $options["evercate_group_id"];

	$apiKey = $options["evercate_api_key"];
	$apiClient = new EvercateApiClient($apiKey);
	$userGroup = $apiClient->GetUserGroup($selectedGroupId);

	$firstName = NULL;
	$lastName = NULL;
	$userName = NULL;
	$selectedTags = array();
	$automaticTags = $form->TagIds;

	foreach($_POST as $name => $value)
	{
		switch($name)
		{
			case "First_Name" : 
				$firstName = $value;
				break;
			case "Last_Name" : 
				$lastName = $value;
				break;
			case "Email_address" : 
				$userName = $value;
				break;
		}
		
		$tagTypePrefix = 'tagType-';

		if(substr($name, 0, strlen($tagTypePrefix)) === $tagTypePrefix)
		{
			$tagTypeId = substr($name, strlen($tagTypePrefix));

			$fullTagType = NULL;
			foreach($userGroup->EvercateTagTypes as $evercateTagType)
			{
				if($evercateTagType->Id == $tagTypeId)
				{
					$fullTagType = $evercateTagType;
					break;
				}
			}
			
			if($fullTagType === NULL)
			{
				$message = "A form had a selecteable tag type with id: ".$tagTypeId.", however no tagtype with this id was found in Evercate.";
				send_error_mail($message);
				//We simply skip this tag type
				continue;
			}
			
			$fullTag = NULL;
			foreach($fullTagType->EvercateTags as $evercateTag)
			{
				if($evercateTag->Id == $value)
				{
					$fullTag = $evercateTagType;
					break;
				}
			}

			if($fullTag == NULL)
			{
				$message = "A form had  a selecteable tag type with id: ".$tagTypeId." with a tag with id: ".$value." however no tag with this id was found in Evercate.";
				send_error_mail($message);
				//We simply skip this tag type
				continue;
			}
			
		 	$selectedTags[] = $value;
		}
	}

	if($firstName === NULL || $lastName === NULL || $userName === NULL)
	{
		wp_send_json_error(array( 'Message' => "Not all fields were filled in" ), 500 );	
	}

	

	$existingUserId = 0;
	$existingUserTags = array();

	try {
		
		$user = $apiClient->GetUser($userName);
		
		if($user !== NULL)
		{
			if($user->GroupId != $selectedGroupId)
			{
				$message = "A person tried to sign up using email/username ".$userName." which was found in Evercate but on a different group than selected. The selected user group is ".$selectedGroupId." and the user was found on ".$user->GroupId.".";
				send_error_mail($message);
				wp_send_json_error(array( 'Message' => "User existed on another group" ), 500 );	
			}
			else
			{
				$existingUserId = $user->Id;
				$existingUserTags = $user->UserTags;
			}
		}

	} catch (Exception $e) {
		$message = "When checking if user with username ".$userName." existed we ran into an error from Evercate. The error: ".$e->getMessage();
		send_error_mail($message);
		wp_send_json_error(array( 'Message' => "Could not check if user already existed" ), 400 );	
	}

	$allTags = array_merge($existingUserTags, $selectedTags, $automaticTags);
	//You can only be assined a tag once
	$uniqueTags = array_values(array_map('intval', array_unique($allTags)));

	$model = new EvercateUser();
	$model->Id = $existingUserId;
	$model->Username = $existingUserId == 0 ? $userName : NULL;
	$model->ExistingUsername = $existingUserId > 0 ? $userName : NULL;
	$model->FirstName = $firstName;
	$model->LastName = $lastName;
	$model->GroupId = $selectedGroupId;
	$model->UserTags = $uniqueTags;

	$payload = json_encode($model);


	try {
			
		$savedUser = $apiClient->saveUser($model);

		$repository->saveSignup($formId, $payload, $existingUserId > 0, 200, NULL);
		
	} catch (Exception $e) {

		$message = $e->getMessage();

		$repository->saveSignup($formId, $payload, $existingUserId > 0, 500, $message);

		$message = "When saving a user from a signup we encountered an error. Error message: ".$e->getMessage() . " - User data: $model";
		send_error_mail($message);
		wp_send_json_error(array( 'Message' => "Failed to save user" ), 400 );		
	}

	
	
	
	wp_send_json_success( NULL, 200 );
}

add_action( 'wp_ajax_evercate-signup-submit', 'signup_submit_handle' );
add_action( 'wp_ajax_nopriv_vercate-signup-submit', 'signup_submit_handle' );

function send_error_mail($message)
{
	$options = get_option("evercate-signup_options");
	$notificationEmail = $options["notification_email"];

	global $wp;
	$current_url = add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) );
	$message .= " - Failure happened on: ". $current_url;
	wp_mail($notificationEmail, "[Error] Evercate signup wordpress plugin", $message);	
}