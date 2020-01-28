<?php

include('../../dependances/class/base.php');

$gestion_articles = new gestion_articles($bdd);
$panel_admin = new panel_admin($bdd, $gestion_adherents, $gestion_articles, $gestion_mails);

if ($gestion_adherents->isConnected())
{
    if ($gestion_adherents->authRole($_SESSION['Adherent'], adherent::ROLE_CA))
    {
        $admin_adherent = $_SESSION['Adherent'];
        $show_panel = true;
        
    }
    else
    {
        MessagePage("Cet espace est réservé au adhérent ayant au minimum le role CA.");
        die();
    }
}
else
{
    $_SESSION['lastpage'] = 'https://admin.bde-bp.fr';
    header('Location: https://auth.bde-bp.fr');
}

?>