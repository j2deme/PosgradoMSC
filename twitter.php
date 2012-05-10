<?php
//ini_set ('error_reporting', E_ALL);

require_once './vendor/twitter/twitter.class.php';

$consumerKey 	= "bhqtdonKSjPec3lObO7h4w";
$consumerSecret = "bB2ohDnvAQJZExhbics52LTpe1vuf0mAeHR4dqizo";
$accessToken	= "13940702-T0sQk1WKxeb6gJB7rqDEnnR2tdNbwgEWcey5Rwpyk";
$accessTokenSecret = "YFac9K1zDeVMKD93VhDaRM0C6J4BrHdqAyTl8qYNis";

$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
$status = $twitter->send('Hola este es un tweet de prueba');

echo $status ? 'OK' : 'ERROR';

?>