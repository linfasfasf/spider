<?php

class admincontroller extends controller{
	public function __construct(){
		parent::__construct();
		echo "this is admincontroller !";
	}

	public function index(){
		echo  "this is admincontroller->index()";
	}
}
