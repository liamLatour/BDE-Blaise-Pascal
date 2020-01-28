<?php
var_dump($_REQUEST);

$req = json_encode($_REQUEST, JSON_PRETTY_PRINT);

if (file_exists("callback_campagne.txt"))
{
    $last = file_get_contents("callback_campagne.txt");
}

file_put_contents("callback_campagne.txt", $req."\n\n\n\n\n".$last);
?>