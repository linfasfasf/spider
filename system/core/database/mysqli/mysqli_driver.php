<?php

class LP_DB_mysqli extends LP_DB {

	public $hostname;

	public $port;

	public $username;

	public $password;

	public $database;

	public $char_set = 'utf8';

	public $dbcollat = 'utf8_general_ci';

	public $autoinit = TRUE;

	public $conn_id	 = FALSE;

	public function __construct($param){
		foreach($param as $key =>$val){
			$this->$key	= $val;
		}
	}

	public function init(){
		if($this->port != ''){
			return mysqli_connect($this->hostname, $this->username, $this->password,$this->database, $this->port);
		}else{
			return mysqli_connect($this->hostname, $this->username, $this->password, $this->database);
		}
		
	}
}
