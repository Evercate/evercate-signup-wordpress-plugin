<?php

class EvercateUser
{
    public function __construct()
    {
		$this->UserTags = array();
    }

	//For update
	public $Id;

	//For create
	public $Username;

	//For update
	public $ExistingUsername;

	public $FirstName;

	public $LastName;

	public $GroupId;

	public $UserTags;
}
