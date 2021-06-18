<?php

class FormListModel
{
    public function __construct()
    {
        $this->Forms = array();
		$this->Errors = array();
    }

	public $Forms;

	public $Errors;
}
