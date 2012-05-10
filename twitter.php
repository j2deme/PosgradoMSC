<?php
require_once './vendor/twitter.class.php';

$twitter = new Twitter('j2deme', 'delgadomedina');
$status = $twitter->send('@WanaDeWich Hola');

echo $status ? 'OK' : 'ERROR';

?>