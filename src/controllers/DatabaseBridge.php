<?php
$realpath = realpath(dirname(__FILE__)) . '/';
include_once $realpath."../config/config.inc.php";

class DatabaseBridge 
{
	public function __construct()
	{
	}
	
	public function __destruct()
	{
	}
	
	private function toJSON( $arr )
	{
		return json_encode(iterator_to_array( $arr, true ));
	}
	
	public function connect()
	{
		global $DB_DATA;
		
		$connection = new mysqli($DB_DATA["DB_HOST"], $DB_DATA["DB_USER"], $DB_DATA["DB_PASS"], $DB_DATA["DB_NAME"]);

		if ($connection->connect_error)
			die("{\"status\":\"error\",\"message\":\"".$connection->connect_error."\"}");
		
		return $connection;
	}
	
	public function doQuery( $query, $to_json = true )
	{
		$conn   = $this->connect();
		$result = $conn->query( $query );
		$error  = $conn->error;
		$conn->close();
		
		if( $result && !$to_json )
			return $result;
		else if( $result && $to_json )
			return $this->toJSON( $result );
		else
			throw new Exception( $error );
	}
}

?>