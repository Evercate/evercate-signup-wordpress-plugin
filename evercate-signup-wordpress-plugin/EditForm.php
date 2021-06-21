<?php

require_once('EvercateApiClient.php');
require_once('Model/FormModel.php');
require_once('Repository.php');

class EditForm
{
	private $repository = NULL;
	private $apiClient = NULL;
	private $userGroupId = NULL;

	private $firstnameLabel = NULL;
	private $lastnameLabel = NULL;
	private $usernameLabel = NULL;

    public function __construct()
    {
		
        add_action( 'admin_menu', array($this, 'add_to_menu') );
		add_action( 'admin_post_edit_form', array($this, 'process_edit_form') );
		add_action( 'admin_enqueue_scripts', array($this, 'editForm_enqueue_scripts'), 2000 );
		
		//Future extension warning for assigned tags/tag types that no longer exist in evercate
		//add_action( 'admin_notices', array($this, 'missing_tags_warning') );

		$options = get_option("evercate-signup_options");

		$this->firstnameLabel = $options["first_name_default_label"];
		$this->lastnameLabel = $options["last_name_default_label"];
		$this->usernameLabel = $options["username_default_label"];

		if(
			isset($_GET['page']) && $_GET['page'] === "evercate-signup-edit-form" &&
			(
				empty($options["evercate_api_key"]) || 
				empty($options["evercate_group_id"]) || 
				empty($options["notification_email"]))
			)
		{
			add_action( 'wp_loaded', array($this, 'redirect_to_settings') );
		}
			

		$apiKey = $options["evercate_api_key"];
		$this->userGroupId = $options["evercate_group_id"] ?? 0;

		$this->repository = new Repository();
		$this->apiClient = new EvercateApiClient($apiKey);
    }

	public function redirect_to_settings()
	{
		
		wp_redirect( esc_url( wp_nonce_url( add_query_arg( array('page' => 'evercate-signup-settings'), 'admin.php' ), 'settings' ) ) );
		exit;
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

	// public function missing_tags_warning() {
	// 	?->
	// 	<div class="notice notice-error is-dismissible">
	// 		<p><-?php _e( 'This form was linked to either tags or tag types that no longer exists. Save to remove those tag and tag types', 'evercate-signup-wordpress-plugin' ); ?-></p>
	// 	</div>
	// 	<-?php
	// }
	
	
	public function process_edit_form() {

		$model = new FormModel();
		$model->Id = $this->getIfSet($_REQUEST['form_id'], 0);
		$model->Name = $this->getIfSet($_REQUEST['name']);
		$model->FirstNameLabel = $this->getIfSet($_REQUEST['first_name_label']);
		$model->LastNameLabel = $this->getIfSet($_REQUEST['last_name_label']);
		$model->UsernameLabel = $this->getIfSet($_REQUEST['username_label']);
		$model->TagIds = $this->getIfSet($_REQUEST['tags'], array());

		$tagTypeArray = array();
		
		if(isset($_REQUEST['tagtypes']) && is_array($_REQUEST['tagtypes']))
		{
			$userGroup = $this->apiClient->GetUserGroup($this->userGroupId);

			if($userGroup === NULL)
			{
				wp_die("Usergroup configured to use was not found in usergroups sent out by API");
			}

			$tagTypeIds = $_REQUEST['tagtypes'];
			foreach($tagTypeIds as $tagTypeId)
			{
				foreach($userGroup->EvercateTagTypes as $tagType)
				{
					if($tagType->Id == $tagTypeId)	
					{
						$tagTypeArray[$tagType->Id] = $tagType->Name;
					}
				}				
			}
		}
		
		$model->TagTypes = $tagTypeArray;

		$model = $this->repository->saveForm($model);

		wp_redirect( esc_url( wp_nonce_url( add_query_arg( array('page' => 'evercate-signup'), 'admin.php' ), 'evercate-signup-forms' ) ) );
		exit;
	}

    public function edit_form_page()
    {
		$userGroup = $this->apiClient->GetUserGroup($this->userGroupId);

		if($userGroup === NULL)
		{
			wp_die("Usergroup configured to use was not found in usergroups sent out by API");
		}

		$formId = intval($_REQUEST['id']);
		$model = new FormModel();

		if($formId > 0)
		{
			$model = $this->repository->getForm($formId);
			$this->firstnameLabel = $model->FirstNameLabel;
			$this->lastnameLabel = $model->LastNameLabel;
			$this->usernameLabel = $model->UsernameLabel;

		}

		?>
		<div class="wrap">
			<h1><?php echo($formId > 0 ? "Edit form" : "New form") ?></h1>

			<form action="/wp-admin/admin-post.php" method="post">
				<input type="hidden" name="action" value="edit_form">
				<input type="hidden" name="form_id" value="<?php echo($formId) ?>">

				<table class="form-table" role="presentation">
					<tbody>
						<tr class="form-field form-required">
							<th scope="row">
								<Label for="name">Name</Label>
							</th>
							<td>
								<input type="text" name="name" id="name" value="<?php echo($model->Name) ?>" required style="width:25em;" />
							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row">
								<Label for="first_name_label">First namel label</Label>
							</th>
							<td>
								<input type="text" name="first_name_label" id="first_name_label" value="<?php echo($this->firstnameLabel) ?>" required style="width:25em;" />
							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row">
								<Label for="last_name_label">Last name label</Label>
							</th>
							<td>
								<input type="text" name="last_name_label" id="last_name_label" value="<?php echo($this->lastnameLabel) ?>" required style="width:25em;" />
							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row">
								<Label for="username_label">Email/Username label</Label>
							</th>
							<td>
								<input type="text" name="username_label" id="username_label" value="<?php echo($this->usernameLabel) ?>" required style="width:25em;" />
							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row">
								<Label for="tags">Tags to assign to user</Label>
							</th>
							<td>
								<select multiple="multiple" id="tags" name="tags[]" style="width:25em;">
								<?php
									foreach($userGroup->EvercateTagTypes as $tagType)
									{
										echo("<optgroup label='".$tagType->Name."'>");
											foreach($tagType->EvercateTags as $tag)
											{
												$selected = in_array($tag->Id, $model->TagIds) ? "selected" : "";
												echo("<option value='".$tag->Id."' ".$selected.">".$tag->Name."</option>");
											}
										echo("</optgroup>");
									}
								?>
								</select>
							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row">
								<Label for="tagtypes">User selectable tag types</Label>
							</th>
							<td>
								<select multiple="multiple" id="tagtypes" name="tagtypes[]" style="width:25em;">
								<?php
									foreach($userGroup->EvercateTagTypes as $tagType)
									{
										$selectedTag = $model->TagTypes[$tagType->Id];
										$selected = isset($selectedTag) ? "selected" : "";
										echo("<option value='".$tagType->Id."' ".$selected.">".$tagType->Name."</option>");
									}
								?>
								</select>
							</td>
						</tr>
					</tbody>
				</table>	

				
				
				<p class="submit">
					<input type="submit" name="saveform" id="saveform" class="button button-primary" value="Save Form" />
				</p>
			</form>

		</div>
		<?php
    }

	public function editForm_enqueue_scripts( $hook ) {

		wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0-rc.0');
		wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', 'jquery', '4.1.0-rc.0');

		wp_enqueue_script( 
			'editForm-js',                            // Handle
			plugins_url( '/EditForm.js', __FILE__ ),  // Path to file
			array( 'jquery' ),                        // Dependancies
			'1.0.0'                                   // Version
		);
	
	}

	private function getIfSet(&$value, $default = null)
	{
		return isset($value) ? $value : $default;
	}
	

}



if( is_admin() )
    $EvercateSignup_editform_page = new EditForm();