<?php

require_once('EvercateApiClient.php');
require_once('FormsListTable.php');

class Forms
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
            'Forms', 
            'Forms', 
            'edit_pages', 
            'evercate-signup', 
            array( $this, 'forms_page' ),
			100
        );
    }

    public function forms_page()
    {
		$forms_table = new FormsListTable();
		$forms_table->prepare_items();

		$new_form_args = array(
			'page'   => 'evercate-signup-edit-form',
			'action' => 'edit',
			'id'  => 0,
		);

		$addNewButton = sprintf(
			'<a href="%1$s" class="page-title-action">%2$s</a>',
			esc_url( wp_nonce_url( add_query_arg( $new_form_args, 'admin.php' ), 'newitem' ) ),
			_x( 'Add New', 'List table action', 'evercate-signup-wordpress-plugin' )
		);

		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<?php echo($addNewButton); ?>
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
    $EvercateSignup_forms_page = new Forms();