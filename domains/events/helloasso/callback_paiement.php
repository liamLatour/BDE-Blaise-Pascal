<?php
include('../../../dependances/class/base_lite.php');



$req = json_encode($_REQUEST, JSON_PRETTY_PRINT);

if (file_exists("callback_paiement.txt"))
{
    $last = file_get_contents("callback_paiement.txt");
}

file_put_contents("callback_paiement.txt", $req."\n\n\n\n\n".$last);



$gestion_events = new gestion_events($bdd, $gestion_adherents);
$helloasso = new helloasso($bdd, $gestion_events);
$helloasso->newPaiement($_REQUEST);

?>