<?php
include_once("./controllers/DatabaseBridge.php");

class RaidManager 
{
	protected $db;
	
	public function __construct()
	{
		$this->db = new DatabaseBridge();
	}
	
	public function __destruct()
	{
	}
	
	private function errorJSON( $msg )
	{
		return "{\"status\":\"error\", \"message\":\"".$msg."\"}";
	}
	
	public function getNearestRaidsFrom( $lat, $lng, $dist = 1 )
	{
		$query = "CALL getNearestRaidsFrom( ".$lat.", ".$lng.", ".$dist.");";
		
		try 
		{
			$raids = $this->db->doQuery( $query );			
		}
		catch( Exception $e )
		{
			return $this->errorJSON( $e->getMessage() );
		}
		
		return "{\"status\":\"ok\", \"raids\":".$raids."}";
	}
	
	public function getRaidsInArea( $lat1, $lng1, $lat2, $lng2 )
	{
		$query = "CALL getRaidsInArea( ".$lat1.", ".$lng1.", ".$lat2.", ".$lng2.");";
		
		try 
		{
			$raids = $this->db->doQuery( $query );			
		}
		catch( Exception $e )
		{
			return $this->errorJSON( $e->getMessage() );
		}
		
		return "{\"status\":\"ok\", \"raids\":".$raids."}";
	}
	
	public function insertRaid( $lat, $lng, $level, $client_time, $start_time = NULL, $countdown = NULL, $pokemon = NULL )
	{
        if ( !$start_time && !$countdown )
            return $this->errorJSON( "<span>Almeno un campo tra <code>Orario di Inizio</code> e <code>Minuti Rimanenti</code> deve essere compilato.</span>" );

        if ( !$start_time && $countdown && !$pokemon )
            return $this->errorJSON( "<span>Se il raid &egrave; gi&agrave; iniziato inserire il nome del Pok&eacute;mon.</span>" );
		
		$client_datetime   = new DateTime();
		$client_datetime->setTimestamp((int)$client_time);
		
		if( !$start_time && $countdown && $client_time )
		{
			$countdown_di    = new DateInterval("PT".( 60 - (int)$countdown )."M" );
			$start_datetime  = clone $client_datetime;
			
			$start_datetime->sub( $countdown_di );
		}
		else
		{
			$start_time_split = explode( ":", $start_time );			
			$start_datetime   = new DateTime();
			$start_datetime->setTimestamp( (int)$client_time );
			$start_datetime->setTime( $start_time_split[0], $start_time_split[1], 0 );
		
			if( $client_datetime > $start_datetime )
				return $this->errorJSON( "Il tempo di inizio del Raid non pu&ograve; essere precedente ad ora. Purtroppo non abbiamo un TARDIS." );
		}
		
		$time_str = $start_datetime->format("Y-m-d H:i:s");
		$poke_str = $pokemon ? "'".$pokemon."'" : "NULL";
		
		$query = "INSERT INTO raids (raid_level, raid_start_time, raid_pokemon, raid_latitude, raid_longitude )".
				            "VALUES ( ".$level.", '".$time_str."', ".$poke_str.", ".$lat.", ".$lng.");";
		
		try 
		{
			$insert = $this->db->doQuery( $query, false );			
		}
		catch( Exception $e )
		{
			return $this->errorJSON( $e->getMessage() );
		}
		
		return "{\"status\":\"ok\"}";
	}
}
?>