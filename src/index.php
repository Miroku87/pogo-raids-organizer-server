<?php
ini_set('html_errors', false);
session_start();

date_default_timezone_set('Europe/Rome');

$realpath = realpath(dirname(__FILE__)) . '/';
include($realpath."controllers/RaidManager.php");

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');

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
	else if ( $what == "raidinfo" )
	{
		echo $rm->getRaidInfo( $_GET["raid_id"] );
	}
	else if ( $what == "userpartecipations" )
	{
		echo $rm->getUserPartecipations( $_GET["user_id"] );
	}
}
else if ( $action == "insert" )
{
	if( $what == "raidinfo" )
	{
		echo $rm->insertRaid( $post->lat, $post->lon, $post->raidLevel, $post->clientTime, $post->raidStartTime, $post->raidCountdown, $post->raidPokemon );
	}
	else if( $what == "raidpartecipation" )
	{
		echo $rm->insertAttendee( $_GET["user_id"], $_GET["raid_id"] );
	}
}
else if ( $action == "remove" )
{
	if( $what == "raidpartecipation" )
	{
		echo $rm->removeAttendee( $_GET["user_id"], $_GET["raid_id"] );
	}
}
else if ( $action == "do" )
{
	if( $what == "clearraidssession" )
	{
		echo $rm->clearRaidsSession( );
	}
}
?>