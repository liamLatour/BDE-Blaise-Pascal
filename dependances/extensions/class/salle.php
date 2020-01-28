<?php


class salle
{

    const   JOUR_LUNDI      = 'lundi',
            JOUR_MARDI      = 'mardi',
            JOUR_MERCREDI   = 'mercredi',
            JOUR_JEUDI      = 'jeudi',
            JOUR_VENDREDI   = 'vendredi',
            JOUR_SAMEDI     = 'samedi';







    private $_IDH,
            $_Nom,
            $_Heure,
            $_Plage,
            $_Trust,
            $_Pop,
            $_Contributeur;

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
    public function setIDH($value) { $this->_IDH = $value; }
    public function setNom($value) { $this->_Nom = $value; }
    public function setHeure($value) { $this->_Heure = $value; }
    public function setPlage($value) { $this->_Plage = $value; }
    public function setTrust($value) { $this->_Trust = $value; }
    public function setPop($value) { $this->_Pop = $value; }
    public function setContributeur($value) { $this->_Contributeur = $value; }

    // GETTERS
    public function getIDH() { return $this->_IDH; }
    public function getNom() { return $this->_Nom; }
    public function getHeure() { return $this->_Heure; }
    public function getPlage() { return $this->_Plage; }
    public function getTrust() { return $this->_Trust; }
    public function getPop() { return $this->_Pop; }
    public function getContributeur() { return $this->_Contributeur; }













}



?>