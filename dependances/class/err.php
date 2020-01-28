<?php
/**
 * Class ERR
 * 
 * Permet de gerer les erreurs retournées par les fonctions
 * rajouter les nouveaux types d'erreurs ici en constantes int
 */

 class err
 {
    private $_value;

    const   INC             = 999,

            NEXISTE_PAS     = 1,
            PASS_EXISTE     = 2,
            ALLINFOSNEEDD   = 3,

            BFORM_EMAIL     = 10,
            BFORM_PASS      = 11,
            BFORM_NOM       = 12,
            BFORM_PRENOM    = 13,
            BFORM_CLASSE    = 14,
            BFORM_STATUS    = 15,
            BFORM_ROLE      = 16,

            NUNI_EMAIL      = 20,
            NUNI_PNAME      = 21,

            INV_VALIDCODE   = 31,
            
            WRONG_PASS      = 40,
            WRONG_INFOS     = 41,
            STATUS_ANNULE   = 42,
            
            BDD_CHANGEMAIL  = 50,
            BDD_CREATEADH   = 51,
            BDD_PASS        = 52,
            BDD_ERROR       = 53,
            
            DEJA_CONNECTE   = 60,

            ARTFORM_MISSPARAM       = 70,
            ARTFORM_BADTYPE         = 71,
            ARTFORM_IDORJSONFORM    = 72,
            ARTFORM_OPTIONS         = 73,
            ARTFORM_NOINPUTS        = 74,

            ADMIN_FCNFORBID = 80,
            ADMIN_BADJSON   = 81,
            ADMIN_JSONWRITE = 82,

            BAD_JOURHEURE   = 90,
            NO_SALLES       = 91,
            SALLE_EXISTE    = 92,
            NOT_LOGIN       = 93,
            SALLE_EXISTE_PAS    = 94,
            BAD_ACTION      = 95,
            BAD_SALLE       = 96,

            IMG_NAME        = 101,
            IMG_TYPE        = 102;

    public function __construct(int $value)
    {
        $this->_value = $value;
    }

    public function g() // recuperer l'erreur
    {
        return $this->_value;
    }

    // verifier si il y a une erreur de retournée en value
    // true si pas d'erreur
    // false si erreur
    public static function c($value) // check
    {
        if ($value === true)
        {
            return true;
        }
        else if (is_array($value) && sizeof($value) > 0)
        {
            if(is_a($value[0], 'err'))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            if(is_a($value, 'err'))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
    }

    public static function e(int $value) // creer une erreur
    {
        return new err($value);
    }

    public static function print($erreurs)
    {
        if ($erreurs != NULL && is_array($erreurs))
        {
            if (sizeof($erreurs) > 1)
            {
                $pre = '- ';
            }
            else
            {
                $pre = '';
            }
            foreach ($erreurs as $erreur)
            {
                echo '<p style="color: rgb(200,50,50); font-weight: bold">'.$pre.$erreur.'</p>';
            }
        }           
    }



 
}




?>