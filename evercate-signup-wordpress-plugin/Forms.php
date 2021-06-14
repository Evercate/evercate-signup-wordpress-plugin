<?php

require_once('EvercateApiClient.php');
require_once('FormsListTable.php');

class Forms
{


    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_to_menu' ) );
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
		$my_list_table = new FormsListTable();
		$my_list_table->prepare_items();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			<form id="forms-filter" method="get">
				<!-- For plugins, we also need to ensure that the form posts back to our current page -->
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<!-- Now we can render the completed list table -->
				<?php $my_list_table->display() ?>
			</form>

		</div>
		<?php
    }

   
}

if( is_admin() )
    $EvercateSignup_forms_page = new Forms();