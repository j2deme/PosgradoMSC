<?php
//ini_set ('error_reporting', E_ALL);

require_once './vendor/twitter/twitter.class.php';

$consumerKey 	= "EeNs24z1jl5nUW5XOlozmQ";
$consumerSecret = "3lxhiQBzzamQzHWSKy4ESmYTZyPllbBoD2pbaEqO8";
$accessToken	= "576620583-SnYj8COxgoboe3V7wxscmrdY2g60MZudyOtet7cr";
$accessTokenSecret = "IWrm5w12XWlGMsaSTs8Hmwxn8iKAHEBKMzzwzE3im4";

$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
$status = $twitter->send('@wanadewich Hola este es un tweet de prueba');

echo $status ? 'OK' : 'ERROR';

?>