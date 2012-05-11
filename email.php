<?php
require_once './vendor/ladybug/Ladybug/Autoloader.php';
Ladybug\Autoloader::register();
require_once './vendor/swiftmailer/swift_required.php';

$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
             ->setUsername('j2deme@gmail.com')
             ->setPassword('delgadomedina');

$mailer = Swift_Mailer::newInstance($transport);

$subject = "FBI is watching you";
$to_addresses = array('luis.hum18@gmail.com');
$body = "<h1>Alert!</h1>";
$body .= "<p>This is a message from the Federal Bureau of Investigation.</p><hr/><p>We are watching you.</p>";
$message = Swift_Message::newInstance($subject)
            ->setFrom(array('investigator@fbi.gov' => 'Captain America'))
            ->setTo($to_addresses)
            ->setBody($body);
 
//Enviamos
$result = $mailer->send($message);


ladybug_dump($result);
?>