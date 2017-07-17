<?php

include "./config/config.inc.php";

class DatabaseBridge 
{
	protected $connection;
	
	public function __construct()
	{
		global $DB_DATA;
		
		// Create connection
		$connection = new mysqli($DB_DATA["DB_HOST"], $DB_DATA["DB_USER"], $DB_DATA["DB_PASS"], $DB_DATA["DB_NAME"]);

		// Check connection
		if ($connection->connect_error) 
		{
			die("Connection failed: " . $connection->connect_error);
		} 
		
		echo "Connected successfully<br>";
	}
}

?>