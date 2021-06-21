<?php

require_once('SignupsListTable.php');

class Signups
{


    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_to_menu' ) );

		$options = get_option("evercate-signup_options");

		if(
			isset($_GET['page']) && $_GET['page'] === "evercate-signup" &&
			(
				empty($options["evercate_api_key"]) || 
				empty($options["evercate_group_id"]) || 
				empty($options["notification_email"]))
			)
		{
			add_action( 'wp_loaded', array($this, 'redirect_to_settings') );
		}
    }
	
	public function redirect_to_settings()
	{	
		wp_redirect( esc_url( wp_nonce_url( add_query_arg( array('page' => 'evercate-signup-settings'), 'admin.php' ), 'settings' ) ) );
		exit;
	}

    public function add_to_menu()
    {
        add_submenu_page(
			'evercate-signup',
            'Signups', 
            'Signups', 
			'edit_pages', 
            'evercate-signup-signups', 
            array( $this, 'signups_page' ),
			50
        );
    }

    public function signups_page()
    {
		$forms_table = new SignupsListTable();
		$forms_table->prepare_items();



		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<hr class="wp-header-end">

			<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			<form id="forms-filter" method="get">
				<!-- For plugins, we also need to ensure that the form posts back to our current page -->
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<!-- Now we can render the completed list table -->
				<?php $forms_table->display() ?>
			</form>

		</div>
		<?php
    }

   
}

if( is_admin() )
    $EvercateSignup_signups_page = new Signups();