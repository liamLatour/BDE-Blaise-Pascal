<?php
/**
 * Script chargé avant chaque page qui permet l'initialisation
 * de nombreuses dépendances
 */


$erreurs = NULL;

date_default_timezone_set('Europe/Paris');



// Charger toutes les classes sans devoir les include/require
function chargerClasse($classe)
{
  require $classe . '.php';
}
spl_autoload_register('chargerClasse');

// Maintenance
if (settings::p('maintenance') == true)
{
    echo "<h1>Le site est en maintenance</h1>";
    die();
}









/**
 * FONCTIONS DE BASE
 */
function stripAccents($str)
{
    return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}
function stripAccentsSep($str)
{
    return str_replace('-', '', str_replace('_', '', str_replace('-', '', str_replace(' ', '', stripAccents($str)))));
    //return str_replace(str_split('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'),str_split('aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'), $stripAccents);
}
function MessagePage($msg, $error = false)
{
    $_SESSION['MessagePage']['msg'] = $msg;
    $_SESSION['MessagePage']['error'] = $error;
    header('Location: https://bde-bp.fr/message.php');
}
function isBetween($int, $min, $max)
{
    return $int >= $min && $int <= $max;
}
function slugify($text)
{
  $text = stripAccents($text);
  
    // replace non letter or digits by -
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  // trim
  $text = trim($text, '-');

  // remove duplicate -
  $text = preg_replace('~-+~', '-', $text);

  // lowercase
  $text = strtolower($text);

  return $text;
}
function checkSlug($string)
{
    return preg_match("/^[a-z0-9]+(?:-[a-z0-9]+)*$/", $string);
}
function random_word()
{
    $file = new SplFileObject(__DIR__."/dico_fr.txt");
    if (!$file->eof())
    {
        $file->seek(mt_rand(1, 22740));
        $contents = $file->current(); // $contents would hold the data from line x
    }
    fclose($file);
    // return strtoupper(str_replace(["\n", "\r"], "", stripAccents($contents)));
    return str_replace(["\n", "\r"], "", utf8_decode($contents));
}
function random_pronounceable_word( $length = 8 ) {
   
    // consonant sounds
    $cons = array(
        // single consonants. Beware of Q, it's often awkward in words
        'b', 'c', 'd', 'f', // 'g', // 'h', 
        'j', // 'k', 
        'l', 'm',
        'n', 'n', 'n', 'p', 'r', 'r', 's', 's', 's', 't', 'v', // 'w', 'x', 'z',
        // possible combinations excluding those which cannot start a word
        // 'gl',
        'pr', 
        'gr', 'ch', // 'ph', // 'ps', 
        'st', // 'th',
        // 'qu'
    );
   
    // consonant combinations that cannot start a word
    $cons_cant_start = array(
        // 'ck',
        'dr', 
        // 'gn',
        // 'ls', 'lt', 'lr',
        // 'mp', 'mt', 'ms',
        // 'ng', 'ns', 'nt',
        // 'rd', 'rg', 'rs', 'rt', 'rn', 'rm',
        'st', // 'sc',
        'ts', 'tch',
    );
   
    // wovels
    $vows = array(
        // single vowels
        'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'o', 'u', // 'y',
        // vowel combinations your language allows
        // 'oe', 'oa', 'oo',
    );
   
    // start by vowel or consonant ?
    $current = ( mt_rand( 0, 1 ) == '0' ? 'cons' : 'vows' );
   
    $word = '';
       
    while( strlen( $word ) < $length ) {
   
        // After first letter, use all consonant combos
        if( strlen( $word ) == 2 )
            $cons = array_merge( $cons, $cons_cant_start );
 
        // random sign from either $cons or $vows
        $rnd = ${$current}[ mt_rand( 0, count( ${$current} ) -1 ) ];
       
        // check if random sign fits in word length
        if( strlen( $word . $rnd ) <= $length ) {
            $word .= $rnd;
            // alternate sounds
            $current = ( $current == 'cons' ? 'vows' : 'cons' );
        }
    }
   
    return strtoupper($word);
}






/**
 * DEMARAGE DE LA SESSION
 */
$currentCookieParams = session_get_cookie_params();

$rootDomain = '.bde-bp.fr';

session_set_cookie_params(
    $currentCookieParams["lifetime"],
    $currentCookieParams["path"],
    $rootDomain,
    $currentCookieParams["secure"],
    $currentCookieParams["httponly"]
);


session_start();




/**
 * 
 * 
 * RECUPERATION IP
 * 
 * 
 * 
 */

// Recuperer IP
if (!empty($_SERVER['REMOTE_ADDR']))
{
    $ip = $_SERVER['REMOTE_ADDR'];
}
elseif (!empty($_SERVER['HTTP_CLIENT_IP']))
{
    $ip = $_SERVER['HTTP_CLIENT_IP'];
}
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
{
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
elseif (!empty($_SERVER['HTTP_X_REAL_IP']))
{
    $ip = $_SERVER['HTTP_X_REAL_IP'];
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










/**
 * CLASSES DE BASE
 */

// Gestion mails
require_once(__DIR__ . '/../vendor/autoload.php');
$gestion_mails = new gestion_mails($ip);



$bdd = new bdd();
//si la bdd n'est pas accessible alors renvoyer vers une version statique du site
if ($bdd->getErrors() != NULL)
{
    gestion_logs::Log('-', log::TYPE_ERROR, 'BDD_ERROR', $bdd->getErrors());
    echo "Une erreur de Base de Donnée est survenue, pour des raisons de sécurité le site ne sera pas accessible tant que cette erreur ne sera pas réglée.";
    die();
}





$gestion_adherents = new gestion_adherents($bdd, $gestion_mails);
$gestion_images = new gestion_images($bdd);



// Test de connection
if ($gestion_adherents->isConnected())
{
    $_SESSION['Adherent'] = $gestion_adherents->getAdherent($_SESSION['Adherent']->getIDA());
}














/**
 * TEST SI MOBILE
 */
$ua = $_SERVER["HTTP_USER_AGENT"];
if (preg_match('/iphone/i',$ua) || preg_match('/android/i',$ua) || preg_match('/blackberry/i',$ua) || preg_match('/symb/i',$ua) || preg_match('/ipad/i',$ua) || preg_match('/ipod/i',$ua) || preg_match('/phone/i',$ua) )
{
    $mobile = true;
}
else
{
    $mobile = false;
}




/**
 * COMPTAGE DES VISITES
 */
$req_visites = $bdd->web()->query('SELECT * FROM visites WHERE Date = CURDATE()');
$req_visites = $req_visites->fetch(PDO::FETCH_ASSOC);
$visite_jour = $req_visites['Num'];
if (!isset($_SESSION['visit']))
{
    if ($req_visites)
    {
        $up = $bdd->web()->prepare('UPDATE visites SET Num = :Num WHERE ID = :ID');
        $up->execute([
            'ID' => $req_visites['ID'],
            'Num' => $req_visites['Num'] + 1
        ]);

        $visite_jour = $req_visites['Num'] + 1;
    }
    else
    {
        $up = $bdd->web()->prepare('INSERT INTO visites (Num, Date) VALUES(:Num, CURDATE())');
        $up->execute([
            'Num' => 1
        ]);

        $visite_jour = 1;
    }
    
    $_SESSION['visit'] = true;
}






/**
 * CONNEXION AUTOMATIQUE
 */
$gestion_adherents->loginConnectionCookie();


?>