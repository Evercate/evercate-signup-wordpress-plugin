<?php

class EvercateUserGroup
{
    public function __construct()
    {
        $this->EvercateTagTypes = array();
		$this->AllEvercateTags = array();
    }

	public $Id;

	public $Name;

	public $EvercateTagTypes;

	public $AllEvercateTags;
}