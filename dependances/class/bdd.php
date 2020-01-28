<?php
/**
 * Class BDD
 * 
 * Stocke tout les objets bdd pour les differentes base necessaires
 * Les objets sont construit à sa construction
 * 
 * Pour modifier les identifiants, les modifier dans ses attributs ci-dessous
 * Possibilités plus tard de les rendres dynamique
 */


class bdd
{
    private $_web,
            $_registre,
            $_salleop,
            $_error,
            $_settings,
            

// identifiants des bdd

            // $_web_login = [
            //     'host_name' => 'localhost',
            //     'database' => 'web',
            //     'user_name' => 'root',
            //     'password' => '16-Dsc.Paul'
            // ],

            // $_salleop_login = [
            //     'host_name' => 'localhost',
            //     'database' => 'salleop',
            //     'user_name' => 'root',
            //     'password' => '16-Dsc.Paul'
            // ],

            // $_registre_login = [
            //     'host_name' => 'localhost',
            //     'database' => 'registre',
            //     'user_name' => 'root',
            //     'password' => '16-Dsc.Paul'
            // ];

            $_web_login = [
                'host_name' => 'db747760417.db.1and1.com',
                'database' => 'db747760417',
                'user_name' => 'dbo747760417',
                'password' => 'GLC-bp63'
            ],

            $_salleop_login = [
                'host_name' => 'db5000234703.hosting-data.io',
                'database' => 'dbs229201',
                'user_name' => 'dbu461467',
                'password' => 'GLC-bp63'
            ],

            $_registre_login = [
                'host_name' => 'db747760724.db.1and1.com',
                'database' => 'db747760724',
                'user_name' => 'dbo747760724',
                'password' => 'GLC-bp63'
            ];


// Construction de la classe, creation des objets bdd

    public function __construct()
    {
        try
        {
            $this->_web = new PDO('mysql:host='.$this->_web_login['host_name'].'; dbname='.$this->_web_login['database'].';', $this->_web_login['user_name'], $this->_web_login['password']);
            $this->_salleop = new PDO('mysql:host='.$this->_salleop_login['host_name'].'; dbname='.$this->_salleop_login['database'].';', $this->_salleop_login['user_name'], $this->_salleop_login['password']);
            $this->_registre = new PDO('mysql:host='.$this->_registre_login['host_name'].'; dbname='.$this->_registre_login['database'].';', $this->_registre_login['user_name'], $this->_registre_login['password']);
        }
        catch(Exception $e)
        {
            $this->_error = $e->getMessage();
        }
    }

    public function getErrors()
    {
        return $this->_error;
    }

// Recuperer objets bdd

    public function web()
    {
        return $this->_web;
    }

    public function registre()
    {
        return $this->_registre;
    }

    public function salleop()
    {
        return $this->_salleop;
    }
}

?>
