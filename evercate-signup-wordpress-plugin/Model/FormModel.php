<?php

class FormModel
{
    public function __construct()
    {
        $this->TagIds = array();
		$this->TagTypeIds = array();
    }

	public $Id;

	public $Name;

	public $TagIds;

	public $TagTypeIds;

	public $FirstNameLabel;

	public $LastNameLabel;

	public $UsernameLabel;
}
