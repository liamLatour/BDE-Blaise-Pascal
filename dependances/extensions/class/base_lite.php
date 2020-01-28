<?php
date_default_timezone_set('Europe/Paris');


function chargerClasse($classe)
{
  require $classe . '.php';
}
spl_autoload_register('chargerClasse');


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
 * CLASS DE BASE
 */
// Gestion mails
require_once(__DIR__ . '/../vendor/autoload.php');
$gestion_mails = new gestion_mails($ip);
// Base de donnée
$bdd = new bdd();
if ($bdd->getErrors() != NULL)
{
    die();
}
// Gestion adhérents
$gestion_adherents = new gestion_adherents($bdd, $gestion_mails);



?>