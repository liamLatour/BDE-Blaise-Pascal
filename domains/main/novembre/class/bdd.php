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
    private $_registre,
            $_error,
            

// identifiants des bdd

            // $_registre_login = [
            //     'host_name' => 'db747760724.db.1and1.com',
            //     'database' => 'db747760724',
            //     'user_name' => 'dbo747760724',
            //     'password' => 'GLC-bp63'
            // ];

            $_registre_login = [
                'host_name' => 'localhost',
                'database' => 'registre',
                'user_name' => 'root',
                'password' => '16-Dsc.Paul'
            ];

// Construction de la classe, creation des objets bdd

    public function __construct()
    {
        try
        {
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


    public function registre()
    {
        return $this->_registre;
    }

}

?>
