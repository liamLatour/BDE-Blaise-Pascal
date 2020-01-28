<?php
/**
 * Class GESTION_ADHERENTS
 * 
 * objectif:
 * gestion des adherents
 * creation, modification, recupereration, stockage, 
 * traiter les adherents qui ont payé par le web et par checque
 * affichage et formattage des adherents
 * 
 */


class gestion_adherents
{

    private $_bdd,
            $_settings;





    /**
     * CONSTRUCTION
     */
    public function __construct(bdd $bdd)
    {
        $this->_settings = settings::p('gestion_adherents');
        $this->_bdd = $bdd;
    }



    public function isConnected()
    {
        return (isset($_SESSION['Connected']));
    }
    public function logout()
    {
        //unset($_SESSION);
        session_destroy();
    }







    /** =======================================================================================
     * RECUPERER ET CREER UN OBJET ADHERENT
     * Renvoi un adherent ou false
     */

    // créer une class:adherent depuis un resultat mysql
    // renvoi un adherent ou false
    public function createAdherentFromReq($req)
    {
        if (isset($req['ID'])) // verifier qu'on a bien un retour
        {
            $donnes_adherent = [
                'Nom' => $req['nom'],
                'Prenom' => $req['prenom'],
                'Classe' => $req['classe'],
                'Email' => $req['email'],
                'IDA' => $req['identifiant'],
                'ID' => $req['ID']
            ];
            $adherent = new adherent($donnes_adherent);
            return $adherent;
        }
        else
        {
            return false;
        }
    }

    // recuperer un adh depuis son IDA
    public function getAdherent(int $IDA)
    {
        $req = $this->_bdd->registre()->prepare('SELECT * FROM registre_provisoire WHERE identifiant = :IDA');
        $req->execute([
            'IDA' => $IDA
        ]);
        $req = $req->fetch(PDO::FETCH_ASSOC);

        return $this->createAdherentFromReq($req);
    }

    // recuperer un adh depuis son ID (plus securisé car pas publique)
    public function getAdherentFromID(int $ID)
    {
        $req = $this->_bdd->registre()->prepare('SELECT * FROM registre_provisoire WHERE ID = :ID');
        $req->execute([
            'ID' => $ID
        ]);
        $req = $req->fetch(PDO::FETCH_ASSOC);

        return $this->createAdherentFromReq($req);
    }

    // recuperer un adh depuis son Nom et Prenom
    public function getAdherentFromPN(string $Nom, string $Prenom)
    {
        $req = $this->_bdd->registre()->prepare('SELECT * FROM registre_provisoire WHERE prenom = :Prenom AND nom = :Nom');
        $req->execute([
            'Prenom' => $Prenom,
            'Nom' => $Nom
        ]);
        $req = $req->fetch(PDO::FETCH_ASSOC);

        return $this->createAdherentFromReq($req);
    }

    // recuperer un adh depuis son Email
    public function getAdherentFromMail(string $Email)
    {
        $req = $this->_bdd->registre()->prepare('SELECT * FROM registre_provisoire WHERE email = :Email');
        $req->execute([
            'Email' => $Email
        ]);
        $req = $req->fetch(PDO::FETCH_ASSOC);

        return $this->createAdherentFromReq($req);
    }









    








   
    /** =======================================================================================
     * VERIFICATIONs
     */
    public function verifyEmailForm(string $email)
    {
        return preg_match("/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/", $email);
    }


    // PNom correspond au prenom ou au nom -> on applique la même verification
    public function verifyPNom(string $PNom)
    {
        return preg_match("/^([A-Za-z- àéèëêïîç]){3,30}$/", $PNom);
    }

    public function verifyClasse($classe)
    {
        $all_classes = $this->_settings['classes'];
        return in_array($classe, $all_classes);
    }




}

?>