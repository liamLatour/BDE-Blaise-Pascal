<?php
/**
 * Script chargé avant chaque page qui permet l'initialisation
 * de nombreuses dépendances
 */
session_start();




// Charger toutes les classes sans devoir les include/require
function chargerClasse($classe)
{
  require $classe . '.php';
}
spl_autoload_register('chargerClasse');


$erreurs = NULL;


// Classes de base
$bdd = new bdd();
$gestion_adherents = new gestion_adherents($bdd);

//Fonctions de base
function stripAccents($str)
{
    return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    //return str_replace(str_split('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'),str_split('aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'), $stripAccents);
}
function stripAccentsSep($str)
{
    return str_replace('-', '', str_replace('_', '', str_replace('-', '', str_replace(' ', '', stripAccents($str)))));
    //return str_replace(str_split('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'),str_split('aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'), $stripAccents);
}

if (settings::p('maintenance') == true && !(isset($_GET['pass']) && $_GET['pass'] == 'SCK'))
{
    header('Location: maintenance');
    die();
}

settings::p('bdd');


// Recuperer IP
if (!empty($_SERVER['HTTP_X_REAL_IP']))
{
    $ip = $_SERVER['HTTP_X_REAL_IP'];
}
elseif (!empty($_SERVER['HTTP_CLIENT_IP']))
{
    $ip = $_SERVER['HTTP_CLIENT_IP'];
}
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
{
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
elseif (!empty($_SERVER['REMOTE_ADDR']))
{
    $ip = $_SERVER['REMOTE_ADDR'];
}
else
{
    $ip = 'NotFound';
}



/**
 * Recuperer l'ip et la stocker dans la session
 * Si l'ip change on reinitialise la session pour eviter le vol de session
 * (car l'id de session est stockée en cookie)
 */
if (!isset($_SESSION['IP']))
{
    $_SESSION['IP'] = $ip;
}
else
{
    if ($_SESSION['IP'] != $ip)
    {
        session_destroy();
        $_SESSION['IP'] = $ip;
    }

}


//si la bdd n'est pas accessible alors renvoyer vers une version statique du site
if ($bdd->getErrors() != NULL)
{
    gestion_logs::Log($_SESSION['IP'], log::TYPE_ERROR, 'Erreur BDD', $bdd->getErrors());
    echo 'Une erreur de base de donnée est survenue, nous faisons notre possible pour la régler dans les plus bref delais.';
    die();
}

// Client est mobile ?
$ua = $_SERVER["HTTP_USER_AGENT"];
if (preg_match('/iphone/i',$ua) || preg_match('/android/i',$ua) || preg_match('/blackberry/i',$ua) || preg_match('/symb/i',$ua) || preg_match('/ipad/i',$ua) || preg_match('/ipod/i',$ua) || preg_match('/phone/i',$ua) )
{
    $mobile = true;
}
else
{
    $mobile = false;
}


?>
