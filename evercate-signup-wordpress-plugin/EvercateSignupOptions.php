<?php

require_once('EvercateApiClient.php');

class EvercateSignupOptions
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
	
	private $groups = array();

	private $apiError = NULL;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );		
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Evercate signup', 
            'Evercate signup', 
            'manage_options', 
            'evercate-signup', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'evercate-signup_options' );
	
		if(!empty($this->options['evercate_api_key']))
		{			
			$apiClient = new EvercateApiClient($this->options['evercate_api_key']);

			try {
				$response = $apiClient->GetUserGroups();

				foreach ($response as $group) {
					$this->groups[$group->Id] = $group->Name;
				}
				var_dump($this->groups);

			} catch (Exception $e) {
				$this->apiError = $e->getMessage();
				
			}
			
		}
		
        ?>
        <div class="wrap">
            <h1>Evercate signup - settings</h1>
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
            '', // Title
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
        
    }
}

if( is_admin() )
    $EvercateSignup_settings_page = new EvercateSignupOptions
();