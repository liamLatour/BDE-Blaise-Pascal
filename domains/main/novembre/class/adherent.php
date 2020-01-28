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

                $_Email;


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

    // GETTERS
    public function getID() { return $this->_ID; }
    public function getIDA() { return $this->_IDA; }
    public function getNom() { return $this->_Nom; }
    public function getPrenom() { return $this->_Prenom; }
    public function getClasse() { return $this->_Classe; }
    public function getEmail() { return $this->_Email; }


}

?>