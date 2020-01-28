<?php
include('../../dependances/class/base.php');
$gestion_events = new gestion_events($bdd, $gestion_adherents);

if (isset($_GET['i']))
{
    $inscription = $gestion_events->getInscription($_GET['i']);
    if (is_null($inscription->ID_adherent))
    {
        if (isset($_GET['login']) && $_GET['login'] == $inscription->Email)
        {
            $valid_access = true;
        }
        else
        {
            $valid_access = false;
            echo "WRONG LOGIN";
        }
    }
    else if ($gestion_adherents->isConnected() && $inscription->ID_adherent == $_SESSION['Adherent']->getIDA())
    {
        $valid_access = true;
    }
    else
    {
        $valid_access = false;
        echo "CAN'T ACCESS THAT";
    }
}
else
{
    $valid_access = false;
}


if ($valid_access)
{
    $paiements = [];
    foreach($inscription->paiements_IDs as $ID_paiement)
    {
        $paiements[] = $gestion_events->getPaiement($inscription->event_slug, $ID_paiement);
    }
    
    echo '<pre>';
    var_dump($paiements);
    echo '</pre>';
    echo '<hr>';
    echo '<pre>';
    var_dump($inscription);
    echo '</pre>';
}

?>