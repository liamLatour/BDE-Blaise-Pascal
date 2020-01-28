<?php
/**
 * Class LOG
 * 
 * 
 * Elle represente une ligne de log
 * elle contient donc les infos d'un log (voir attributs privés)
 * elle est controlée par class:gestion_logs
 * elle permet de recuperer un array
 * ou un array sérialisé de son contenu (objectif: le stocker dans la bdd)
 * 
 * Ses attributs sont récuperés à sa construction
 * à l'exption du datetime qui est initialisé à sa construction
 * 
 * 
 * Pour régénerer un log passé, le construire avec ses attributs
 * et utiliser log->pushDateTime($date) pour remettre le datetime d'origine
 * 
 * 
 * 
 */



class log
{

    private $_date,
            $_time,
            $_ip,
            $_type,
            $_titre,
            $_description,
            $_IDA;

    const   ARRAY_IP            = 1,
            ARRAY_TIME          = 2,
            ARRAY_IDA           = 3,
            ARRAY_TYPE          = 4,
            ARRAY_TITRE         = 5,
            ARRAY_DESCRIPTION   = 6,

            TYPE_VIEW           = 1,
            TYPE_COMPTE         = 2,
            TYPE_ADMIN          = 3,
            TYPE_ERROR          = 4,
            TYPE_EVENT          = 5;

    /**
     * CONSTRUCTION
     */
    public function __construct(string $ip, int $type, string $titre, $description, int $IDA)
    {
        date_default_timezone_set('Europe/Paris');
        $this->_date = date('Ymd');
        $this->_time = date('H:i:s');
        $this->_type = $type;
        $this->_titre = $titre;
        if ($description != '')
        {
            $this->_description = base64_encode(serialize($description));
        }
        else
        {
            $this->_description = '';
        }
        $this->_IDA = $IDA;
        $this->_ip = $ip;
    }

    public function pushDateTime(date $date) // Forcer un temps passé pour regenerer un log passé
    {
        $this->_time = date_format('H:i:s', $date);
        $this->_date = date_format('Ymd', $date);
    }



    /**
     * RECUPERER CONTENU DU LOG
     */
    public function getLine()
    {
        $array = $this->getArray();
        $line = serialize($array);
        return $line;
    }

    public function getArray()
    {
        // En utilisation des constantes qui sont des ints de 1 à 9 
        // la serialisation du tableau sera plus compacte et tiendra
        // moins de place dans la bdd
        $array = [
            self::ARRAY_IP => $this->_ip,
            self::ARRAY_TIME => $this->_time,
            self::ARRAY_IDA => $this->_IDA,
            self::ARRAY_TYPE => $this->_type,
            self::ARRAY_TITRE => $this->_titre,
            self::ARRAY_DESCRIPTION => $this->_description
        ];
        return $array;
    }

    public function getDate() // Retourne un objet date -> pour le stoquer dans la bdd
    {
        //$date = date_create_from_format('Ymd', $this->_date);
        return $this->_date;
    }

}


?>