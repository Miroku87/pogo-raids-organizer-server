<?php
//Credits to https://github.com/Flynsarmy/PHPWebSocket-Chat

// prevent the server from timing out
set_time_limit(0);

// include the web sockets server script (the server is started at the far bottom of this file)

$realpath = realpath(dirname(__FILE__)) . '/';
include_once $realpath.'class.PHPWebSocket.php';
include_once $realpath.'DatabaseBridge.php';

function saveChatMessageToDB( $message_data )
{
	$db   = new DatabaseBridge();
	$conn = $db->connect();
	
	$query = "INSERT INTO raids_chat (raid_id_chat, id_fb_user_chat, username_chat, picture_url_user_chat, message_chat, timestamp_chat) VALUES ('".$message_data->raid_id_chat."','".$message_data->id_fb_user_chat."','".$conn->real_escape_string($message_data->username_chat)."','".$message_data->picture_url_user_chat."','".$conn->real_escape_string($message_data->message_chat)."', '".$message_data->timestamp_chat."')";
	
	$conn->close();
	
	try 
	{
		$db->doQuery( $query, false );
	}
	catch( Exception $e )
	{
		echo "{\"status\":\"error\", \"message\":\"".$e->getMessage()."\"}";
		return false;
	}
	
	echo "{\"status\":\"ok\"}";
	return true;
}

function makeSystemMessageJSON( $message )
{
	return '{"type":"chatMessage","data":{"username_chat":"Sistema","id_fb_user_chat":0,"picture_url_user_chat":"","message_chat":"'.$message.'","timestamp_chat":'.round(microtime(true) * 1000).'}}';
}

// when a client sends data to the server
function wsOnMessage($clientID, $message, $messageLength, $binary) 
{
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );

	// check if message length is 0
	if ($messageLength == 0) {
		$Server->wsClose($clientID);
		return;
	}
	
	saveChatMessageToDB( json_decode( $message )->data );

	//The speaker is the only person in the room. Don't let them feel lonely.
	if ( sizeof($Server->wsClients) > 1 )
		foreach ( $Server->wsClients as $id => $client )
			if ( $id != $clientID )
				$Server->wsSend($id, $message);
}

// when a client connects
function wsOnOpen($clientID)
{
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );

	$Server->log( "$ip ($clientID) has connected." );
	
	//Send a join notice to everyone but the person who joined
	/*foreach ( $Server->wsClients as $id => $client )
		if ( $id != $clientID )
			$Server->wsSend($id, makeSystemMessageJSON( "Visitor $clientID ($ip) has joined the room." ) );*/
}

// when a client closes or lost connection
function wsOnClose($clientID, $status) 
{
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );

	$Server->log( "$ip ($clientID) has disconnected." );

	//Send a user left notice to everyone in the room
	/*foreach ( $Server->wsClients as $id => $client )
		$Server->wsSend($id, makeSystemMessageJSON( "Visitor $clientID ($ip) has left the room." ) );*/
}

// start the server
$Server = new PHPWebSocket();
$Server->bind('message', 'wsOnMessage');
$Server->bind('open', 'wsOnOpen');
$Server->bind('close', 'wsOnClose');
// for other computers to connect, you will probably need to change this to your LAN IP or external IP,
// alternatively use: gethostbyaddr(gethostbyname($_SERVER['SERVER_NAME']))
$Server->wsStartServer('localhost', $argv[1]);


?>