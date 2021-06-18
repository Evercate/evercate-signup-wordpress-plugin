<?php

class FormModel
{
    public function __construct()
    {
        $this->TagIds = array();
		$this->TagTypes = array();
    }

	public $Id;

	public $Name;

	public $TagIds;

	public $TagTypes;

	public $FirstNameLabel;

	public $LastNameLabel;

	public $UsernameLabel;

	public $Created;
}
