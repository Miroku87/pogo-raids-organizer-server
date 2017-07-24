<?php
include_once("./controllers/DatabaseBridge.php");

function compareRaidsRecords( $record_a, $record_b )
{
	return (int)$record_a["raid_id"] - (int)$record_b["raid_id"];
}

class RaidManager 
{
	protected $db;
	
	public function __construct()
	{		
		$this->db = new DatabaseBridge();
		
		if( !isset( $_SESSION["showed_raids"] ) )
			$_SESSION["showed_raids"] = array();
	}
	
	public function __destruct()
	{
	}
	
	private function errorJSON( $msg )
	{
		return "{\"status\":\"error\", \"message\":\"".$msg."\"}";
	}
	
	public function clearRaidsSession( )
	{
		try 
		{
			$_SESSION["showed_raids"] = array();
		}
		catch( Exception $e )
		{
			return $this->errorJSON( $e->getMessage() );
		}
		
		return "{\"status\":\"ok\"}";
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
			$raids = $this->db->doQuery( $query, false );			
		}
		catch( Exception $e )
		{
			return $this->errorJSON( $e->getMessage() );
		}
		
		$raids_array = iterator_to_array( $raids, true );
		//echo var_dump($_SESSION["showed_raids"]);
		
		$array_diff_1 = array_udiff( $raids_array, $_SESSION["showed_raids"], "compareRaidsRecords" );
		$array_diff_2 = array_udiff( $_SESSION["showed_raids"], $raids_array, "compareRaidsRecords" );
		
		
		$real_diff    = array_merge( $array_diff_1, $array_diff_2 );
		$_SESSION["showed_raids"] = $raids_array;
		
		return "{\"status\":\"ok\", \"raids\":".json_encode($real_diff)."}";
	}
	
	public function getRaidInfo( $raid_id )
	{
		$query = "SELECT *, NOW() > raid_start_time AS raid_start_time_elapsed, raid_start_time + INTERVAL 1 HOUR AS raid_end_time FROM raids WHERE raid_id = '".$raid_id."'";
		
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
	
	public function getUserPartecipations( $user_id )
	{
		$query = "SELECT id_raid_partecipation FROM partecipations WHERE id_fb_user_partecipation = '".$user_id."';";
		
		try 
		{
			$results = $this->db->doQuery( $query );
		}
		catch( Exception $e )
		{
			return $this->errorJSON( $e->getMessage() );
		}
		
		return "{\"status\":\"ok\", \"partecipations\":".$results."}";
	}
	
	public function insertAttendee( $user_id, $raid_id )
	{
		$query = "INSERT INTO partecipations (id_raid_partecipation, id_fb_user_partecipation) VALUES ('".$raid_id."', '".$user_id."');";
		
		try 
		{
			$results = $this->db->doQuery( $query, false );
		}
		catch( Exception $e )
		{
			return $this->errorJSON( $e->getMessage() );
		}
		
		return "{\"status\":\"ok\"}";
	}
	
	public function removeAttendee( $user_id, $raid_id )
	{
		$query = "DELETE FROM partecipations WHERE id_raid_partecipation = '".$raid_id."' AND id_fb_user_partecipation = '".$user_id."';";
		
		try 
		{
			$results = $this->db->doQuery( $query, false );
		}
		catch( Exception $e )
		{
			return $this->errorJSON( $e->getMessage() );
		}
		
		return "{\"status\":\"ok\"}";
	}
	
	public function insertRaid( $lat, $lng, $level, $client_time, $start_time = NULL, $countdown = NULL, $pokemon = NULL )
	{		
		$client_datetime   = new DateTime();
		$client_datetime->setTimestamp((int)$client_time);
		
        if ( !$start_time && !$countdown )
            return $this->errorJSON( "<span>Almeno un campo tra <code>Orario di Inizio</code> e <code>Minuti Rimanenti</code> deve essere compilato.</span>" );

        if ( !$start_time && $countdown && !$pokemon )
            return $this->errorJSON( "<span>Se il raid &egrave; gi&agrave; iniziato inserire il nome del Pok&eacute;mon.</span>" );
		
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
			
			$diff             = $start_datetime->diff($client_datetime);
			
			if( $diff->h >= 2 )
				return $this->errorJSON( "Il Raid non pu&ograve iniziare fra pi&ugrave; di due ore." );
		
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