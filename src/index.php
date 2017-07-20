<?php
ini_set('html_errors', false);

date_default_timezone_set('Europe/Rome');

include("./controllers/RaidManager.php");

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

//http://blogs.shephertz.com/2014/05/21/how-to-implement-url-routing-in-php/

if( count($_GET) > 0 )
{
	$action = $_GET["action"];
	$what   = $_GET["what"];
}
else
{
	$post   = json_decode( $_POST["json"] );
	$action = $post->action;
	$what   = $post->what;
}

$rm = new RaidManager();

if( $action == "get" )
{
	if( $what == "nearestraids" )
	{
		$coords = explode( ",", $_GET["coords"] );
		
		if( count($coords) > 2 )
			echo $rm->getRaidsInArea( $coords[0], $coords[1], $coords[2], $coords[3] );
		else 
			echo $rm->getNearestRaidsFrom( $coords[0], $coords[1] );
	}
}
else if ( $action == "insert" )
{
	if( $what == "raidinfo" )
	{
		//echo $post->lat.", ".$post->lon.", ".$post->raidLevel.", ".$post->raidStartTime.", ".$post->clientTime.", ".$post->raidCountdown.", ".$post->raidPokemon;
		echo $rm->insertRaid( $post->lat, $post->lon, $post->raidLevel, $post->clientTime, $post->raidStartTime, $post->raidCountdown, $post->raidPokemon );
	}
}
?>