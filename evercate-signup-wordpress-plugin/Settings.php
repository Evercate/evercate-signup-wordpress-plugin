<?php

require_once('EvercateApiClient.php');

class Settings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
	
	private $groups = array();

	private $apiError = NULL;
	private $groupError = NULL;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_to_menu' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );		
    }

    /**
     * Add options page
     */
    public function add_to_menu()
    {
        // This page will be under "Settings"
        add_submenu_page(
			'evercate-signup',
            'Settings', 
            'Settings', 
            'manage_options', 
            'evercate-signup-settings', 
            array( $this, 'settings_page' ),
			100
        );
    }

    /**
     * Options page callback
     */
    public function settings_page()
    {
        // Set class property
        $this->options = get_option( 'evercate-signup_options' );


		if(!empty($this->options['evercate_api_key']))
		{			
			$apiClient = new EvercateApiClient($this->options['evercate_api_key']);

			try {
				$userGroups = $apiClient->GetUserGroups();

				$selectedGroupFound = false;
				foreach ($userGroups as $group) {
					$this->groups[$group->Id] = $group->Name;

					if($group->Id === $this->options['evercate_group_id'])
						$selectedGroupFound = true;
				}
				
				if(!empty($this->options['evercate_group_id']) && !$selectedGroupFound)
					$this->groupError = "The group you had selected no longer exists";

			} catch (Exception $e) {
				$this->apiError = $e->getMessage();
				
			}
			
		}
		
        ?>
        <div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'evercate-signup_option_group' );
                do_settings_sections( 'evercate-signup_setting_page' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {      	

        register_setting(
            'evercate-signup_option_group', // Option group
            'evercate-signup_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'integration_section', // ID
            'Required settings', // Title
            NULL, // Callback
            'evercate-signup_setting_page' // Page
        );  

        add_settings_field(
            'evercate_api_key', // ID
            'Evercate API key', // Title 
            array( $this, 'evercate_api_key_callback' ), // Callback
            'evercate-signup_setting_page', // Page
            'integration_section' // Section           
        );      

        add_settings_field(
            'notification_email', 
            'Notification email', 
            array( $this, 'notification_email_callback' ), 
            'evercate-signup_setting_page', 
            'integration_section'
        );      
		
		add_settings_field(
            'evercate_group_id', // ID
            'Group to add users to', // Title 
            array( $this, 'evercate_group_id_callback' ), // Callback
            'evercate-signup_setting_page', // Page
            'integration_section' // Section           
        );

		add_settings_section(
            'default_values', // ID
            'Default values', // Title
            NULL, // Callback
            'evercate-signup_setting_page' // Page
        );  

		add_settings_field(
            'first_name_default_label', // ID
            'First name default', // Title 
            array( $this, 'first_name_default_label_callback' ), // Callback
            'evercate-signup_setting_page', // Page
            'default_values' // Section           
        );  
		
		add_settings_field(
            'last_name_default_label', // ID
            'Last name default', // Title 
            array( $this, 'last_name_default_label_callback' ), // Callback
            'evercate-signup_setting_page', // Page
            'default_values' // Section           
        ); 

		add_settings_field(
            'username_default_label', // ID
            'Email/Username default', // Title 
            array( $this, 'username_default_label_callback' ), // Callback
            'evercate-signup_setting_page', // Page
            'default_values' // Section           
        ); 

		add_settings_field(
            'success_title', // ID
            'Success message title', // Title 
            array( $this, 'success_title_callback' ), // Callback
            'evercate-signup_setting_page', // Page
            'default_values' // Section           
        ); 

		add_settings_field(
            'success_message', // ID
            'Success message', // Title 
            array( $this, 'success_message_callback' ), // Callback
            'evercate-signup_setting_page', // Page
            'default_values' // Section           
        ); 

		add_settings_field(
            'error_title', // ID
            'Error message title', // Title 
            array( $this, 'error_title_callback' ), // Callback
            'evercate-signup_setting_page', // Page
            'default_values' // Section           
        ); 

		add_settings_field(
            'error_message', // ID
            'Error message', // Title 
            array( $this, 'error_message_callback' ), // Callback
            'evercate-signup_setting_page', // Page
            'default_values' // Section           
        ); 

		add_settings_field(
            'send_button_label', // ID
            'Send button label', // Title 
            array( $this, 'send_button_label_callback' ), // Callback
            'evercate-signup_setting_page', // Page
            'default_values' // Section           
        ); 


    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
		
		if( isset( $input['evercate_api_key'] ) )
            $new_input['evercate_api_key'] = sanitize_text_field( $input['evercate_api_key'] );
		
        if( isset( $input['notification_email'] ) )
            $new_input['notification_email'] = sanitize_email( $input['notification_email'] );
		
		if( isset( $input['evercate_group_id'] ) )
            $new_input['evercate_group_id'] = absint( $input['evercate_group_id'] );

		if( isset( $input['first_name_default_label'] ) )
            $new_input['first_name_default_label'] = sanitize_text_field( $input['first_name_default_label'] );

		if( isset( $input['last_name_default_label'] ) )
            $new_input['last_name_default_label'] = sanitize_text_field( $input['last_name_default_label'] );

		if( isset( $input['username_default_label'] ) )
            $new_input['username_default_label'] = sanitize_text_field( $input['username_default_label'] );

		if( isset( $input['success_title'] ) )
            $new_input['success_title'] = sanitize_text_field( $input['success_title'] );
		if( isset( $input['success_message'] ) )
            $new_input['success_message'] = sanitize_text_field( $input['success_message'] );

		if( isset( $input['error_title'] ) )
            $new_input['error_title'] = sanitize_text_field( $input['error_title'] );			
		if( isset( $input['error_message'] ) )
            $new_input['error_message'] = sanitize_text_field( $input['error_message'] );


		if( isset( $input['send_button_label'] ) )
            $new_input['send_button_label'] = sanitize_text_field( $input['send_button_label'] );			

        return $new_input;
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function evercate_api_key_callback()
    {
		$val = isset( $this->options['evercate_api_key'] ) ? esc_attr( $this->options['evercate_api_key']) : '';

        printf('<input type="text" id="evercate_api_key" name="evercate-signup_options[evercate_api_key]" value="%s" />', $val);

		if($this->apiError !== NULL)
		{
			print('<p style="color:red">Call to api failed. Most likely the API key given is incorrect.</p>');
		}
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function notification_email_callback()
    {
		$val = isset( $this->options['notification_email'] ) ? esc_attr( $this->options['notification_email']) : '';

        printf('<input type="text" id="notification_email" name="evercate-signup_options[notification_email]" value="%s" />', $val);
    }
	
	 /** 
     * Get the settings option array and print one of its values
     */
    public function evercate_group_id_callback()
    {
		if(empty($this->groups))
		{
			print('<p style="color:red">Enter api key and save to fetch available groups.</p>');
		}
		else
		{
			$val = isset( $this->options['evercate_group_id'] ) ? esc_attr( $this->options['evercate_group_id']) : '';

			$input = '<select id="evercate_group_id" name="evercate-signup_options[evercate_group_id]">';

			if(empty($val))
			{
				$input .= '<option>Select group</option>';
			}

			foreach($this->groups as $groupId => $groupName)
			{
				$selected = $groupId == $val ? 'selected' : '';
				$input .= '<option value="'.$groupId.'" '.$selected.'>'.$groupName.'</option>';
			}
			$input .= '</select>';

			print($input);
			
		}

		if($this->groupError !== NULL)
		{
			print('<p style="color:red">'.$this->groupError.'</p>');
		}   
    }

	public function first_name_default_label_callback()
    {
		$val = isset( $this->options['first_name_default_label'] ) ? esc_attr( $this->options['first_name_default_label']) : '';

        printf('<input type="text" id="first_name_default_label" name="evercate-signup_options[first_name_default_label]" value="%s" />', $val);
    }

	public function last_name_default_label_callback()
    {
		$val = isset( $this->options['last_name_default_label'] ) ? esc_attr( $this->options['last_name_default_label']) : '';

        printf('<input type="text" id="last_name_default_label" name="evercate-signup_options[last_name_default_label]" value="%s" />', $val);
    }

	public function username_default_label_callback()
    {
		$val = isset( $this->options['username_default_label'] ) ? esc_attr( $this->options['username_default_label']) : '';

        printf('<input type="text" id="username_default_label" name="evercate-signup_options[username_default_label]" value="%s" />', $val);
    }

	public function success_title_callback()
    {
		$val = isset( $this->options['success_title'] ) ? esc_attr( $this->options['success_title']) : '';

        printf('<input type="text" id="success_title" name="evercate-signup_options[success_title]" value="%s" />', $val);
    }

	public function success_message_callback()
    {
		$val = isset( $this->options['success_message'] ) ? esc_attr( $this->options['success_message']) : '';

        printf('<textarea type="text" id="success_message" name="evercate-signup_options[success_message]">%s</textarea>', $val);
    }

	public function error_title_callback()
    {
		$val = isset( $this->options['error_title'] ) ? esc_attr( $this->options['error_title']) : '';

        printf('<input type="text" id="error_title" name="evercate-signup_options[error_title]" value="%s" />', $val);
    }

	public function error_message_callback()
    {
		$val = isset( $this->options['error_message'] ) ? esc_attr( $this->options['error_message']) : '';

        printf('<textarea type="text" id="error_message" name="evercate-signup_options[error_message]">%s</textarea>', $val);
    }

	public function send_button_label_callback()
    {
		$val = isset( $this->options['send_button_label'] ) ? esc_attr( $this->options['send_button_label']) : '';

        printf('<input type="text" id="send_button_label" name="evercate-signup_options[send_button_label]" value="%s" />', $val);
    }
}

if( is_admin() )
    $EvercateSignup_settings_page = new Settings();