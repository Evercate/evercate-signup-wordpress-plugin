<?php

require_once('Model/FormModel.php');

class Repository
{

    public function __construct()
    {
    }

    public function getForm($id)
    {
		global $wpdb;

		$form = $wpdb->get_results( 
			$wpdb->prepare(
				"SELECT name FROM {$wpdb->prefix}evercate_signup_form WHERE id=%d", $id) 
		 );

		 return new FormModel();

		 var_dump($form);
	}

	public function createForm($formModel)
    {
		global $wpdb;

		$success = true;

		$wpdb->query('START TRANSACTION');

		//base form
		$success = $success && $wpdb->insert( 
			$wpdb->prefix.'evercate_signup_form', 
			array( 
				'name' => $formModel->Name, 
			), 
			array( 
				'%s'  
			) 
		);

		$formId = $wpdb->insert_id;

		//username/email
		$success = $success && $wpdb->insert( 
			$wpdb->prefix.'evercate_signup_form_field', 
			array( 
				'form_id' => $formId, 
				'type' => 'username',
				'sort_index' => 1,
				'label' => $formModel->UsernameLabel,
			), 
			array( 
				'%d',
				'%s',
				'%d',
				'%s'
			) 
		);

		//firstname
		$success = $success && $wpdb->insert( 
			$wpdb->prefix.'evercate_signup_form_field', 
			array( 
				'form_id' => $formId, 
				'type' => 'firstname',
				'sort_index' => 2,
				'label' => $formModel->FirstNameLabel,
			), 
			array( 
				'%d',
				'%s',
				'%d',
				'%s'
			) 
		);

		//lastname
		$success = $success && $wpdb->insert( 
			$wpdb->prefix.'evercate_signup_form_field', 
			array( 
				'form_id' => $formId, 
				'type' => 'lastname',
				'sort_index' => 3,
				'label' => $formModel->LastNameLabel,
			), 
			array( 
				'%d',
				'%s',
				'%d',
				'%s'
			) 
		);

		$success = $success && $wpdb->insert( 
			$wpdb->prefix.'evercate_signup_form_tag', 
			array( 
				'form_id' => $formId, 
				'tag_id' => 1337
			), 
			array( 
				'%d',  
				'%d'
			) 
		);

		if($success)
		{
			$wpdb->query('COMMIT');
		}
		else
		{
			$wpdb->query('ROLLBACK');
			wp_die("Failed to create form in db");
		}


	}
}
