<?php
ini_set ('error_reporting', E_ALL);

require_once './vendor/twitter.class.php';

$twitter = new Twitter('j2deme', 'delgadomedina');
$status = $twitter->send('@WanaDeWich Hola');

echo $status ? 'OK' : 'ERROR';

?>