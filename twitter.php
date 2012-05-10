<?php
//ini_set ('error_reporting', E_ALL);

require_once './vendor/twitter/twitter.class.php';

$consumerKey 	= "EeNs24z1jl5nUW5XOlozmQ";
$consumerSecret = "3lxhiQBzzamQzHWSKy4ESmYTZyPllbBoD2pbaEqO8";
$accessToken	= "576620583-nj28Tp87ynNA5y7uuSNNniwF2o6oV0GELBiQ71E2";
$accessTokenSecret = "3ZTJXe4ZRi8WuRCRb9wFGlLDwxM1CZTs0UDj8ofDu0";

$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
$status = $twitter->send('@wanadewich Hola este es un tweet de prueba');

echo $status ? 'OK' : 'ERROR';

?>