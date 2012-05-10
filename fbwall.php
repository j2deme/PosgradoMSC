<?php
require './vendor/facebook-sdk/facebook.php';

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
  'appId'  => '185788718209778',
  'secret' => '3286939f19dccd2f7c16047831c95e94',
));

/**************************************************************************************
$touid: Facebook Unique ID of an User. Set the variable to "me" if posting on logged in user's wall
$msg: The Message to be posted above the actual link post
$name: Title of the URL to be posted
$link: Direct (Full) URL of the Link to be posted
$description: A short description text about the post
$pic: Absolute URL of the accompanying image to be posted
$action_name & $action_link: Title & URL of the Action link for the post, see this image:
http://i.imgur.com/JCdGh.png

$facebook: The facebook object which can be obtained from the PHP-SDK
***************************************************************************************/
$touid = "100001816788462";
$title = "Mensaje de Prueba";
$uri = "http://www.google.com.mx/";
$msg = "Hola Luis esto es un msj de prueba";
$desc = "Lorem ipsum dolor em sit ament";
$pic = "http://placehold.it/350x150";
$action_name = "Posgrado MSC";
$action_link = "http://posgradomsc.phpfogapp.com/";


$attachment =  array(
'message' => $msg,
'name' => $title,
'link' => $uri,
'description' => $desc,
'picture'=>$pic,
'actions' => json_encode(array('name' => $action_name,'link' => $action_link))
); 

//Posting to the wall of an user whose Facebook User ID is known
try {
	$result = $facebook->api('/'.$touid.'/feed', 'post', $attachment);
	echo $result;
	$ok = strpos($result, $touid);
	if($ok)
		echo "Posted!";
	else
		echo "Failure";
} catch (FacebookApiException $e) {
	//notify error
	echo "Core Meltdown!";
	echo "<pre>";
	print_r($e);
	echo "</pre>";
}

//Posting to the wall of the currently logged-in Facebook user
try {
	//$result = $facebook->api('/me/feed', 'post', $attachment);
} catch (FacebookApiException $e) {
	//notify error
}