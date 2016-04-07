<?php

class LP_DB_mysql extends LP_DB {

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
		if(is_resource($conn_id) OR is_object($this->conn_id)){
			return TRUE;
		}
		if($this->port != ''){
			$this->hostname .= ":".$this->port;
		}
		$conn_id	= mysql_connect($this->hostname, $this->username, $this->password,true);
		
		if(!$this->conn_id){
			return FALSE;
		}

		if($this->database != ''){
			if(!mysql_select_db($this->database, $this->conn_id)){
				exit('db_unable_to_select '.$this->database);
			}else{
				if(! mysql_set_charset($this->char_set, $this->conn_id)){
					return FALSE;
				}
			}
		}
		return TRUE;
	}
}
