<?php

require_once('EvercateApiClient.php');

class EditForm
{


    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_to_menu' ) );
    }


    public function add_to_menu()
    {
        add_submenu_page(
			null,
            'Edit form', 
            'Edit form', 
            'edit_pages', 
            'evercate-signup-edit-form', 
            array( $this, 'edit_form_page' ),
			100
        );
    }


    public function edit_form_page()
    {
		?>
		<div class="wrap">
			<h1>Edit form</h1>

			wow

		</div>
		<?php
    }

   
}

if( is_admin() )
    $EvercateSignup_editform_page = new EditForm();