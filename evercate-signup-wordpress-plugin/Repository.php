<?php

require_once('Model/FormModel.php');
require_once('Model/FormListModel.php');

class Repository
{

    public function __construct()
    {
    }

    public function getFormsList()
    {
		global $wpdb;
		$formList = array();

		$forms = $wpdb->get_results( 
			"SELECT id, name, created FROM {$wpdb->prefix}evercate_signup_form"
		 );

		 foreach($forms as $form)
		 {
			$formList[] = $this->getFormListModel($form);
		 }

		return $formList;
	}

    public function getForm($id)
    {
		global $wpdb;

		$form = $wpdb->get_results( 
			$wpdb->prepare(
				"SELECT id, name, created FROM {$wpdb->prefix}evercate_signup_form WHERE id=%d", $id) 
		 );

		 return $this->getFormListModel($form[0]);

	}

	public function saveForm($formModel)
    {
		global $wpdb;

		$success = true;
		$sortIndex = 0;

		$wpdb->query('START TRANSACTION');

		$formId = $formModel->Id;

		if($formId == 0)
		{
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
		}
		else
		{
			$wpdb->update( 
				$wpdb->prefix.'evercate_signup_form', 
				array( 
					'name' => $formModel->Name, 
				), 
				array( 'id' => $formId ), 
				array( 
					'%s', 
				), 
				array( '%d' ) 
			);


			//We simply clear off tags and fields and re-add them
			$wpdb->delete(
				$wpdb->prefix.'evercate_signup_form_tag', 
				array(
					'form_id' => $formId 
				),
				array(
					'%d'
				)
			);

			$wpdb->delete(
				$wpdb->prefix.'evercate_signup_form_field', 
				array(
					'form_id' => $formId 
				),
				array(
					'%d'
				)
			);

		}

		//username/email
		$success = $success && $wpdb->insert( 
			$wpdb->prefix.'evercate_signup_form_field', 
			array( 
				'form_id' => $formId, 
				'type' => 'username',
				'sort_index' => ++$sortIndex,
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
				'sort_index' => ++$sortIndex,
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
				'sort_index' => ++$sortIndex,
				'label' => $formModel->LastNameLabel,
			), 
			array( 
				'%d',
				'%s',
				'%d',
				'%s'
			) 
		);

		//Tag types to be choice for user
		if(isset($formModel->TagTypes) && is_array($formModel->TagTypes))
		{
			foreach ($formModel->TagTypes as $tagTypeId => $tagTypeLabel)
			{
				$success = $success && $wpdb->insert( 
					$wpdb->prefix.'evercate_signup_form_field', 
					array( 
						'form_id' => $formId, 
						'type' => 'tag_0-1', //indicates it's a tag type where user can select 0 or 1 tag (select box). Future iterations can contain tag_1 (must be exactly one - radio buttons) or tag_0-many (checkboxes)
						'sort_index' => ++$sortIndex,
						'tag_type_id' => $tagTypeId,
						'label' => $tagTypeLabel,
					), 
					array( 
						'%d',
						'%s',
						'%d',
						'%d',
						'%s'
					) 
				);
			}
		}




		//Tags to assign
		if(isset($formModel->TagIds) && is_array($formModel->TagIds))
		{
			foreach ($formModel->TagIds as $tagId)
			{
				$success = $success && $wpdb->insert( 
					$wpdb->prefix.'evercate_signup_form_tag', 
					array( 
						'form_id' => $formId, 
						'tag_id' => $tagId
					), 
					array( 
						'%d',  
						'%d'
					) 
				);
			}
		}

		

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

	public function deleteForm($id)
	{
		global $wpdb;

		$wpdb->delete(
			$wpdb->prefix.'evercate_signup_form', 
			array(
				'id' => $id 
			),
			array(
				'%d'
			)
		);
	}

	private function getFormListModel($dbForm)
	{
		global $wpdb;

		$model = new FormModel();

		$model->Id = $dbForm->id;
		$model->Name = $dbForm->name;
		$model->Created = $dbForm->created;

		$tagIds = $wpdb->get_results( 
			$wpdb->prepare(
				"SELECT tag_id FROM {$wpdb->prefix}evercate_signup_form_tag WHERE form_id=%d", $dbForm->id) 
		 );
		 foreach($tagIds as $tagId)
		 	$model->TagIds[] = $tagId->tag_id;

		 $fields = $wpdb->get_results( 
			$wpdb->prepare(
				"SELECT type, tag_type_id, label FROM {$wpdb->prefix}evercate_signup_form_field WHERE form_id=%d", $dbForm->id) 
		 );
		 foreach($fields as $field)
		 {
			switch($field->type)
			{
				case 'firstname' :
					$model->FirstNameLabel = $field->label;
					break;
				case 'lastname' :
					$model->LastNameLabel = $field->label;
					break;
				case 'username' :
					$model->UsernameLabel = $field->label;
					break;
				case 'tag_0-1' :
					$model->TagTypes[$field->tag_type_id] = $field->label; //Note that now we will keep using this even if changed in evercate. Possibly smarter to merge from evercate data
					break;
			}
		 }

		return $model;
	}
}
