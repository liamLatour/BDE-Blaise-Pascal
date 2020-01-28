<?php
/**
 * Class SETTINGS
 * 
 * s'occupe de la gestion des parametres du site
 * qui sont stoqués dans settings.json
 * 
 * Les param peuvent être modifier dans le fichier json
 * ou via le panel admin role:admin
 * 
 * Utiliser p($param) pour recuperer la valeur d'un paramètre $param
 * Utiliser e($param, $value) pour editer / creer un paramètre $param et lui attribuer $value
 *  
 */




 class settings
 {

    public static function p(string $param)
    {
        $json = file_get_contents(__DIR__.'/settings.json');
        $array = json_decode($json, true);
        if (isset($array[$param]))
        {
            return $array[$param];
        }
        else
        {
            return NULL;
        }
    }

    public function e(string $param, $value)
    {
        $json = file_get_contents(__DIR__.'/settings.json');
        $array = json_decode($json, true);
        $array[$param] = $value;
        $json = json_encode($array, JSON_PRETTY_PRINT);
        return file_put_contents('settings.json', $json, FILE_USE_INCLUDE_PATH | LOCK_EX); // peut retourner un equivalent a false
        // utiliser === false pour verifier si c bien false et pas 0 par exemple
    }





 }





?>