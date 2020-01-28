<?php

include('./content/panel_base.php');

if (isset($show_panel) && $show_panel && $panel_admin->functionAllowed(panel_admin::FCN_DEV_CONSOLE))
{

if (isset($_POST['ex']) && isset($_POST['command']))
{
$command = $_POST['command'];
// DEBUT CODE

if ($command == "create_privateAdhesionToken")
{
    $return = $gestion_adherents->create_privateAdhesionToken();
}
else if ($command == "emptyreq")
{
    $req = $bdd->registre()->prepare('SELECT * FROM adherents WHERE IDA = 1201');
    $req->execute();
    $req = $req->fetch(PDO::FETCH_ASSOC);

    $return = $req;
}
else if ($command == "word")
{
    for ($i=0; $i <= 20; $i++)
    { 
        $return[] = random_pronounceable_word(5)."-".mt_rand(10, 99);
    }
}
else if ($command == "rword")
{
    for ($i=0; $i <= 10; $i++)
    { 
        $return[] = random_word()."-".random_word();
    }
}
else if ($command == "getEvents_slugsForCampaignID")
{
    $helloasso = new helloasso($bdd);
    $return = $helloasso->getEvents_slugsForCampaignID("1");
}
else
{
    $return = "UNKNOWN_COMMAND";
}




















// FIN CODE
$return_json = json_encode($return, JSON_PRETTY_PRINT);
}
else
{
    $return_json = json_encode("NO_COMMAND", JSON_PRETTY_PRINT);
}
?>

<form action="" method="post">
    <input type="text" name="command" value="" style="width: 500px;"></input><button type="submit" name="ex">Execute</button>
</form>

<hr>
<pre>
<?php echo $return_json?>
</pre>
<?php
}
else
{
echo 'Accès interdit, <a href="bde-bp.fr">retour au site</a>';
}
?>