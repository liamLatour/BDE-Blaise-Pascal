<?php
/**
 * Class ADHERENT
 * 
 * Elle represente un adherent 
 * elle est gérée par class:gestion_adherents
 * 
 * pour l'instant elle ne contient pas de methodes particulières = un simple stockage d'infos
 */
class adherent
{
    // ATTRIBUTS
    private     $_ID,

                $_IDA,

                $_Nom,
                $_Prenom,
                $_Classe,

                $_Email,
                $_Password,

                $_Status,

                $_Role,

                $_Newsletter;

    const       STATUS_INSCRIT  = 1,
                STATUS_ADHERENT = 3,
                STATUS_ANNULE   = 0,

                ROLE_ADHERENT   = 1,
                ROLE_CA         = 2,
                ROLE_BUREAU     = 3,
                ROLE_ADMIN      = 99,

                NEWS_ALLOW      = 1,
                NEWS_DENY       = 0,

                CNCT_WITHPASS   = 1,
                CNCT_ONLYADH    = 2;

    /**
     * CONSTRUCTION
     */
    public function __construct(array $donnees) { $this->hydrate($donnees); }

    // HYDRATATION
    public function hydrate(array $donnees)
    {
        foreach ($donnees as $key => $value)
        {
            $setter = 'set'.ucfirst($key);
            if (method_exists($this, $setter))
            {
                $this->$setter($value);
            }
        }
    }

    // SETTERS
    public function setID($value) { $this->_ID = $value; }
    public function setIDA($value) { $this->_IDA = $value; }
    public function setNom($value) { $this->_Nom = $value; }
    public function setPrenom($value) { $this->_Prenom = $value; }
    public function setClasse($value) { $this->_Classe = $value; }
    public function setEmail($value) { $this->_Email = $value; }
    public function setPassword($value) { $this->_Password = $value; }
    public function setStatus($value) { $this->_Status = $value; }
    public function setRole($value) { $this->_Role = $value; }
    public function setNewsletter($value) { $this->_Newsletter = $value; }

    // GETTERS
    public function getID() { return $this->_ID; }
    public function getIDA() { return $this->_IDA; }

    public function getNom() { return $this->_Nom; }
    public function getPrenom() { return $this->_Prenom; }
    public function getPNom()
    {
        return ucwords($this->getPrenom())." ".ucwords($this->getNom());
    }

    public function getInfos()
    {
        return $this->getIDA()."(".$this->getPNom().")";
    }

    public function getClasse() { return $this->_Classe; }
    public function getEmail() { return $this->_Email; }
    public function getEmailCensored()
    {
        $em   = explode("@", $this->_Email);
        $name = implode(array_slice($em, 0, count($em)-1), '@');
        $len  = floor(strlen($name)/2);

        return substr($name,0, $len) . str_repeat('*', $len) . "@" . end($em);  
    }

    public function getPassword() { return $this->_Password; }

    public function getStatus() { return $this->_Status; }
    public function getStatusString()
    { 
        $status = $this->_Status;
        if ($status == self::STATUS_ADHERENT)
        {
            return "Adhérent";
        }
        else if ($status == self::STATUS_ANNULE)
        {
            return "Annulé";
        }
        else if ($status == self::STATUS_INSCRIT)
        {
            return "Inscrit mais cotisation non réglée";
        }
        else
        {
            return 'Erreur';
        }
    }
    public function getStatusStringShort()
    { 
        $status = $this->_Status;
        if ($status == self::STATUS_ANNULE)
        {
            return "ANNULE";
        }
        else if ($status == self::STATUS_ADHERENT)
        {
            return "ADHERENT";
        }
        else if ($status == self::STATUS_INSCRIT)
        {
            return "INSCRIT";
        }
        else
        {
            return "-";
        }
    }
    public static function getStatusShortStringFromInt(int $int)
    {
        if ($int == self::STATUS_ANNULE)
        {
            return "ANNULE";
        }
        else if ($int == self::STATUS_ADHERENT)
        {
            return "ADHERENT";
        }
        else if ($int == self::STATUS_INSCRIT)
        {
            return "INSCRIT";
        }
        else
        {
            return "-";
        }
    }


    public function getRole() { return $this->_Role; }
    public function getRoleString()
    { 
        $role = $this->_Role;
        switch($role)
        {
            case $role == self::ROLE_ADHERENT:
                return "Adhérent";
                break;
            case $role == self::ROLE_ADMIN:
                return "Administrateur";
                break;
            case $role == self::ROLE_BUREAU:
                return "Membre du bureau";
                break;
            case $role == self::ROLE_CA:
                return "Membre du conseil";
                break;
            default:
                return "Erreur";
                break;
        }
    }
    public function getRoleStringShort()
    { 
        $role = $this->_Role;
        switch($role)
        {
            case $role == self::ROLE_ADMIN:
                return "ADMIN";
                break;
            case $role == self::ROLE_ADHERENT:
                return "ADHERENT";
                break;
            case $role == self::ROLE_BUREAU:
                return "BUREAU";
                break;
            case $role == self::ROLE_CA:
                return "CA";
                break;
            default:
                return "-";
                break;
        }
    }
    public static function getRoleShortStringFromInt(int $int)
    {
        switch($int)
        {
            case $int == self::ROLE_ADMIN:
                return "ADMIN";
                break;
            case $int == self::ROLE_ADHERENT:
                return "ADHERENT";
                break;
            case $int == self::ROLE_BUREAU:
                return "BUREAU";
                break;
            case $int == self::ROLE_CA:
                return "CA";
                break;
            default:
                return "-";
                break;
        }
    }

    public function getNewsletter() { return $this->_Newsletter; }
    public function getNewsletterString()
    { 
        if ($this->_Newsletter == self::NEWS_ALLOW)
        {
            return "Acceptée";
        }
        else
        {
            return "Refusée";
        }
    }
    public static function getNewsletterShortStringFromInt(int $int)
    { 
        if ($int == self::NEWS_ALLOW)
        {
            return "OUI";
        }
        else
        {
            return "NON";
        }
    }


    public function getArray()
    {
        return [
            'IDA' => $this->_IDA,
            'Nom' => $this->_Nom,
            'Prenom' => $this->_Prenom,
            'Classe' => $this->_Classe,
            'Email' => $this->_Email,
            'Role' => $this->_Role,
            'Status' => $this->_Status,
            'Newsletter' => $this->_Newsletter,
            'Password' => $this->_Password
        ];
    }







    

}

?>