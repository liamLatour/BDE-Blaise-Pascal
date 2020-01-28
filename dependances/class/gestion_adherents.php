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
            $_settings,
            $_gestion_mails;

    const CONNECTION_COOKIE_DAYS = 100;





    /**
     * CONSTRUCTION
     */
    public function __construct(bdd $bdd, gestion_mails $gestion_mails)
    {
        $this->_settings = settings::p('gestion_adherents');
        $this->_bdd = $bdd;
        $this->_gestion_mails = $gestion_mails;
    }






    /** =======================================================================================
     * CREATION ET INSCRIPTION DES ADHERENTS
     * 
     * erreurs possibles:
     * BFORM_NOM
     * BFORM_PRENOM
     * BFORM_EMAIL
     * NUNI_EMAIL
     * BFORM_CLASSE
     * BDD_CREATEADH
     * 
     * si aucune erreur renvoi true
     */

    // Creation d'un nouvel adherent (mais pas les verifications d'inscription)
    // renvoi erreur(erreur de bdd) ou true
    private function newAdherent(string $Nom, string $Prenom, string $Classe, string $Email, int $Newsletter)
    {
        $IDA = $this->genIDA();
        
        // Enregistre dans la bdd le nouvel adherent
        // verifie si il a bien été créé
        $req = $this->_bdd->registre()->prepare('INSERT INTO adherents
        (IDA, Nom, Prenom, Classe, Email, Status, Role, Newsletter) 
        VALUES(:IDA, :Nom, :Prenom, :Classe, :Email, :Status, :Role, :Newsletter)');
        $req->execute([
            'IDA' => $IDA,
            'Nom' => $Nom,
            'Prenom' => $Prenom,
            'Classe' => $Classe,
            'Email' => $Email,
            'Status' => adherent::STATUS_INSCRIT,
            'Role' => adherent::ROLE_ADHERENT,
            'Newsletter' => $Newsletter
        ]);

        $adherent = $this->getAdherent($IDA);
        if ($adherent)
        {
            $_SESSION['Adherent'] = $adherent;
            $_SESSION['Connected'] = adherent::CNCT_ONLYADH;
            return true;
        }
        else
        {
            return err::e(err::BDD_CREATEADH);
        }
    }

    // Genere un IDA unique
    private function genIDA() 
    {
        $min = $this->_settings['genIDA_min'];
        $max = $this->_settings['genIDA_max'];

        $IDA = random_int($min, $max);
        while ($this->getAdherent($IDA)) // pas sur
        {
            $IDA = random_int($min, $max); // Pour eviter certains nombres trop evidents
        }
        return $IDA;
    }


    private function inscription(string $nom, string $prenom, string $classe, string $email, string $Newsletter)
    {
        if ($this->verifyPNom($nom))
        {
            if ($this->verifyPNom($prenom))
            {
                $verif_mail = $this->verifyEmail($email);
                if (err::c($verif_mail))
                {
                    if ($this->verifyClasse($classe))
                    {

                        if ($Newsletter == 'oui')
                        {
                            $Newsletter = adherent::NEWS_ALLOW;
                        }
                        else
                        {
                            $Newsletter = adherent::NEWS_DENY;
                        }

                        // envoyer un mail de confirmation
                        return $this->newAdherent($nom, $prenom, $classe, $email, $Newsletter);
                    }
                    else
                    {
                        return err::e(err::BFORM_CLASSE);
                    }
                }
                else
                {
                    return $verif_mail;
                }
            }
            else
            {
                return err::e(err::BFORM_PRENOM);
            }
        }
        else
        {
            return err::e(err::BFORM_NOM);
        }
    }

    public function inscriptionHandler($post)
    {
        if (isset($post['prenom']) && isset($post['nom']) && isset($post['classe']) && isset($post['email']) && isset($post['nl']))
        {
            $prenom = (string) $post['prenom'];
            $nom = (string) $post['nom'];
            $classe = (string) $post['classe'];
            $email = (string) $post['email'];
            $nl = (string) $post['nl'];
            $inscription = $this->inscription($nom, $prenom, $classe, $email, $nl);
            switch ($inscription)
            {
                case $inscription === true:
                    $this->_gestion_mails->mail_Adhesion(settings::p('adhesion')['lien_helloasso'], $_SESSION['Adherent']);
                    return true;
                    break;
                case $inscription->g() == err::BFORM_NOM:
                    $erreurs[] = "Le nom ne doit contenir que des lettres et accents, et faire entre 3 et 30 caractères.";
                    return $erreurs;
                    break;
                case $inscription->g() == err::BFORM_PRENOM:
                    $erreurs[] = "Le prenom ne doit contenir que des lettres et accents, et faire entre 3 et 30 caractères.";
                    return $erreurs;
                    break;
                case $inscription->g() == err::BFORM_EMAIL:
                    $erreurs[] = "L'adresse mail ne remplit pas les conditions usuelles.";
                    return $erreurs;
                    break;
                case $inscription->g() == err::NUNI_EMAIL:
                    $erreurs[] = "L'adresse mail est déjà utilisée.";
                    return $erreurs;
                    break;
                case $inscription->g() == err::BFORM_CLASSE:
                    $erreurs[] = "La classe renseignée n'est pas correcte.";
                    return $erreurs;
                    break;
                case $inscription->g() == err::BDD_CREATEADH:
                    gestion_logs::Log($_SESSION['IP'], log::TYPE_ERROR, 'gestion_adherents-inscriptionHandler', 'BDD_CREATEADH');
                    $erreurs[] = "Erreur de base de donnée, veuillez contacter un administrateur.";
                    return $erreurs;
                    break;
                default:
                    gestion_logs::Log($_SESSION['IP'], log::TYPE_ERROR, 'gestion_adherents-inscriptionHandler', 'INCONNUE');
                    $erreurs[] = "Une erreur inconnue est subvenue, si elle persiste veuillez contacter un administrateur.";
                    return $erreurs;
                    break;
            }
        }
        else
        {
            $erreurs[] = 'Toutes les entrées sont obligatoires.';
            return $erreurs;
        }
    }




    /** =======================================================================================
     * CONNECTION DECONNECTION VERIFICATION 
     * et première connection
     * 
     */



    /**
     * erreurs possibles:
     * WRONG_PASS (egalement retourné si l'adherent n'a pas de pass)
     * NEXISTE_PAS
     * DEJA_CONNECTE
     * 
     * renvoi true
     */
    public function login(int $IDA, string $pass, bool $auth_with_pass = true)
    {
        if (!$this->isConnected())
        {
            $adherent = $this->getAdherent($IDA);
            if ($adherent)
            {
                if ($adherent->getStatus() != adherent::STATUS_ANNULE)
                {
                    if ($auth_with_pass)
                    {
                        if ($this->authPassword($adherent, $pass))
                        {
                            return $adherent;
                        }
                        else
                        {
                            return err::e(err::WRONG_PASS);
                        }
                    }
                    else
                    {
                        $_SESSION['Adherent'] = $adherent;
                        $_SESSION['Connected'] = adherent::CNCT_WITHPASS;
                        return true;
                    }
                }
                else
                {
                    return err::e(err::STATUS_ANNULE);
                }
            }
            else
            {
                return err::e(err::NEXISTE_PAS);
            }
        }
        else
        {
            return err::e(err::DEJA_CONNECTE);
        }
    }
       
    public function loginHandler($post)
    {
        if (isset($post['IDA']) && isset($post['pass']))
        {
            $IDA = (int) $post['IDA'];
            $pass = (string) $post['pass'];
            if (1000 <= $IDA && $IDA <= 9999)
            {
                $login = $this->login($IDA, $pass);
                switch ($login)
                {
                    case is_a($login, 'adherent'):
                        return $login;
                        break;
                    case $login->g() == err::DEJA_CONNECTE:
                        $erreurs[] = 'Vous êtes déjà connecté.';
                        return $erreurs;
                        break;
                    case $login->g() == err::NEXISTE_PAS:
                        $erreurs[] = 'Cet identifiant ne correspond pas à un adherent existant.';
                        return $erreurs;
                        break;
                    case $login->g() == err::WRONG_PASS:
                        $erreurs[] = 'Le mot de passe n\' est pas correct.';
                        return $erreurs;
                        break;
                    case $login->g() == err::STATUS_ANNULE:
                        $erreurs[] = 'Votre adhésion a été annulée. Vous ne pouvez plus accéder à votre espace adhérent. Si c\'est une erreur, contactez un administrateur.';
                        return $erreurs;
                        break;
                    default:
                        gestion_logs::Log($_SESSION['IP'], log::TYPE_ERROR, 'gestion_adherents-loginHandler', 'INCONNUE');
                        $erreurs[] = 'Une erreur inconnue est subvenue, si elle persiste veuillez contacter un administrateur.';
                        return $erreurs;
                        break;
                }
            }
            else
            {
                $erreurs[] = 'L\'identifiant doit être un nombre à 4 chiffres';
                return $erreurs;
            }
        }
        else
        {
            $erreurs[] = 'Toutes les entrées sont obligatoires.';
            return $erreurs;
        }
    }


    /**
     * erreurs possibles:
     * DEJA_CONNECTE
     * NEXISTE_PAS
     * WRONG_INFOS
     * BFORM_PASS
     * BDD_PASS
     * PASS_EXISTE
     * 
     * renvoi class:adherent
     */
    public function firstLogin(int $IDA, string $email, string $pass, $change = false)
    {
        if (!$this->isConnected())
        {
            $adherent = $this->getAdherent($IDA);
            if ($adherent)
            {
                if ($adherent->getStatus() != adherent::STATUS_ANNULE)
                {
                    if($adherent->getPassword() == NULL)
                    {
                        // Change permet de passer outre cette verification
                    if ($adherent->getEmail() == $email || $change)
                        {
                            if ($change)
                            {
                                $adherent = $this->changePassHash($IDA, $pass);
                                if ($adherent)
                                {
                                    return true;
                                }
                                else
                                {
                                    return err::e(err::BDD_PASS);
                                }
                            }
                            else
                            {
                                if ($this->verifyPassForm($pass))
                                {
                                    return true;
                                }
                                else
                                {
                                    return err::e(err::BFORM_PASS);
                                }
                            }
                                
                        }
                        else
                        {
                            return err::e(err::WRONG_INFOS);
                        } 
                    }
                    else
                    {
                        return err::e(err::PASS_EXISTE);
                    }
                }
                else
                {
                    return err::e(err::STATUS_ANNULE);
                }
                    
            }
            else
            {
                return err::e(err::NEXISTE_PAS);
            }
        }
        else
        {
            return err::e(err::DEJA_CONNECTE);
        }
            
    }

    public function firstloginHandler($post)
    {
        if (isset($post['IDA']) && isset($post['email']) && isset($post['pass1']) && isset($post['pass2']))
        {
            $IDA = (int) $post['IDA'];
            $pass1 = htmlspecialchars((string) $post['pass1']);
            $pass2 = htmlspecialchars((string) $post['pass2']);
            $email = htmlspecialchars((string) $post['email']);

            if ($pass1 == $pass2)
            {
                if (1000 <= $IDA && $IDA <= 9999)
                {
                    $firstlogin = $this->firstLogin($IDA, $email, $pass1);
                    switch ($firstlogin)
                    {
                        case $firstlogin === true: // Connection et edition réussie ! pour l'instant l'action est temporaire
                            return true;
                            break;
                        case $firstlogin->g() == err::DEJA_CONNECTE:
                            $erreurs[] = 'Vous êtes déjà connecté.';
                            return $erreurs;
                            break;
                        case $firstlogin->g() == err::NEXISTE_PAS:
                            $erreurs[] = 'Cet identifiant ne correspond pas à un adherent existant.';
                            return $erreurs;
                            break;
                        case $firstlogin->g() == err::WRONG_INFOS:
                            $erreurs[] = 'L\'adresse mail ne correspondent pas avec celle enregistrée.';
                            return $erreurs;
                            break;
                        case $firstlogin->g() == err::BFORM_PASS:
                            $erreurs[] = 'Le mot de passe n\'est pas du bon format.<br>Il doit contenir entre 6 et 32 caractères alphanumériques ou spéciaux (-_.,;?!@/\\*)';
                            return $erreurs;
                            break;
                        case $firstlogin->g() == err::BDD_PASS:
                            gestion_logs::Log($_SESSION['IP'], log::TYPE_ERROR, 'gestion_adherents-firstloginHandler', 'BDD_PASS');
                            $erreurs[] = 'Erreur de base de donnée, veuillez contacter un administrateur.';
                            return $erreurs;
                            break;
                        case $firstlogin->g() == err::PASS_EXISTE:
                            $erreurs[] = 'Un mot de passe est déjà lié à cet adherent, si c\'est une erreur veuillez contacter un administrateur.';
                            return $erreurs;
                            break;
                        case $firstlogin->g() == err::STATUS_ANNULE:
                            $erreurs[] = 'Votre adhésion a été annulée. Vous ne pouvez plus accéder à votre espace adhérent. Si c\'est une erreur, contactez un administrateur.';
                            return $erreurs;
                            break;
                        default:
                            gestion_logs::Log($_SESSION['IP'], log::TYPE_ERROR, 'gestion_adherents-firstloginHandler', 'INCONNUE');
                            $erreurs[] = 'Une erreur inconnue est subvenue, si elle persiste veuillez contacter un administrateur.';
                            return $erreurs;
                            break;
                    }
                }
                else
                {
                    $erreurs[] = 'L\'identifiant doit être un nombre à 4 chiffres';
                    return $erreurs;
                }
            }
            else
            {
                $erreurs[] = 'Le mot de passe et sa vérification ne correspondent pas.';
                return $erreurs;
            }
        }
        else
        {
            $erreurs[] = 'Toutes les entrées sont obligatoires.';
            return $erreurs;
        }
    }

    public function isConnected()
    {
        return (isset($_SESSION['Connected']) && isset($_SESSION['Adherent']) && $_SESSION['Connected'] == adherent::CNCT_WITHPASS && $this->getAdherent($_SESSION['Adherent']->getIDA()));
    }

    public function isRegistered()
    {
        return (isset($_SESSION['Connected']) && isset($_SESSION['Adherent']) && $_SESSION['Connected'] == adherent::CNCT_ONLYADH && $this->getAdherent($_SESSION['Adherent']->getIDA()));
    }

    public function isRegisteredORConnected()
    {
        return $this->isConnected() || $this->isRegistered();
    }

    public function logout()
    {
        unset($_SESSION);
        session_destroy();
        $this->deleteConnectionCookie();
    }


    //cookie

    public function createConnectionCookie($IDA)
    {
        $key = self::createKey(128);

        $req = $this->_bdd->registre()->prepare('INSERT INTO cookie_auth (IDA, CookieKey) VALUES(:IDA, :CookieKey)');
        $res = $req->execute([
            'IDA' => $IDA,
            'CookieKey' => $key,
        ]);

        $value = [
            'KEY' => $key,
            'IDA' => $IDA
        ];

        $value = base64_encode(serialize($value));

        setcookie("ConnectionCookie", $value, time()+self::CONNECTION_COOKIE_DAYS*24*60*60, "/", "bde-bp.fr");
    }

    public function getConnectionCookie()
    {
        if (isset($_COOKIE['ConnectionCookie']))
        {
            $cookie = $_COOKIE['ConnectionCookie'];
            $cookie = unserialize(base64_decode($cookie));
            return $cookie;
        }
        else
        {
            return false;
        }
        
    }

    public function getConnectionCookieBDD($cookie)
    {        
        $req = $this->_bdd->registre()->prepare('SELECT * FROM cookie_auth WHERE CookieKey = :CookieKey');
        $req->execute(['CookieKey' => $cookie['KEY']]);
        $req = $req->fetch(PDO::FETCH_ASSOC);

        if ($req)
        {
            return $req;
        }
        else
        {
            return false;
        }
    }

    public function verifConnectionCookie($cookie_auth, $cookie)
    {
        $today = new DateTime();
        $query_date = new DateTime($cookie_auth['TimeStamp']);

        $diff = $today->diff($query_date);
        $days = $diff->d;
        
        if ($cookie_auth['IDA'] == $cookie['IDA'] && $days <= self::CONNECTION_COOKIE_DAYS)
        {
            return true;
        }
        else
        {
            $this->deleteConnectionCookie();
            return false;
        }
    }

    public function loginConnectionCookie()
    {
        $cookie = $this->getConnectionCookie();
        if ($cookie)
        {           
            $cookie_auth = $this->getConnectionCookieBDD($cookie);
            if ($cookie_auth)
            {
                if ($this->verifConnectionCookie($cookie_auth, $cookie))
                {
                    return $this->login((int) $cookie_auth['IDA'], '', false);
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
        
    }

    public function deleteConnectionCookie()
    {
        $cookie = $this->getConnectionCookie();
        if ($cookie)
        {
            $req = $this->_bdd->registre()->prepare('DELETE FROM cookie_auth WHERE CookieKey = :CookieKey');
            $res = $req->execute(['CookieKey' => $cookie['KEY']]);
            unset($_COOKIE['ConnectionCookie']);
            setcookie("ConnectionCookie", '', time()-1000, "/", "bde-bp.fr");
        }
    }

    public function deleteAllConnectionCookie(int $IDA)
    {
        $req = $this->_bdd->registre()->prepare('DELETE FROM cookie_auth WHERE IDA = :IDA');
        return $req->execute(['IDA' => $IDA]);
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
                'Nom' => $req['Nom'],
                'Prenom' => $req['Prenom'],
                'Classe' => $req['Classe'],
                'Email' => $req['Email'],
                'Password' => $req['Password'],
                'Status' => $req['Status'],
                'Role' => $req['Role'],
                'Newsletter' => $req['Newsletter'],
                'IDA' => $req['IDA'],
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
        $req = $this->_bdd->registre()->prepare('SELECT * FROM adherents WHERE IDA = :IDA');
        $req->execute([
            'IDA' => $IDA
        ]);
        $req = $req->fetch(PDO::FETCH_ASSOC);

        return $this->createAdherentFromReq($req);
    }

    // recuperer un adh depuis son ID (plus securisé car pas publique)
    public function getAdherentFromID(int $ID)
    {
        $req = $this->_bdd->registre()->prepare('SELECT * FROM adherents WHERE ID = :ID');
        $req->execute([
            'ID' => $ID
        ]);
        $req = $req->fetch(PDO::FETCH_ASSOC);

        return $this->createAdherentFromReq($req);
    }

    // recuperer un adh depuis son Nom et Prenom
    public function getAdherentFromPN(string $Nom, string $Prenom)
    {
        $req = $this->_bdd->registre()->prepare('SELECT * FROM adherents WHERE Prenom = :Prenom AND Nom = :Nom');
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
        $req = $this->_bdd->registre()->prepare('SELECT * FROM adherents WHERE Email = :Email');
        $req->execute([
            'Email' => $Email
        ]);
        $req = $req->fetch(PDO::FETCH_ASSOC);

        return $this->createAdherentFromReq($req);
    }

    public function getIDAFromMail(string $mail)
    {
        $adherent = $this->getAdherentFromMail($mail);
        if ($adherent)
        {
            return $adherent->getIDA();
        }
        else
        {
            return false;
        }
    }



    public function isAdherent(int $IDA)
    {
        return (bool) $this->getAdherent($IDA);
    }





    /** =======================================================================================
     * AUTHENTIFICATION
     * verifier la validité du mdp
     * verifier les modifications en fonction des roles
     * et de l'adherent qui fait la requete
     * 
     * renvoi true ou false
     */

    public function authPasswordByIDA(int $IDA, string $pass)
    {
        $adherent = $this->getAdherent($IDA);
        if ($adherent)
        {
            return $this->authPassword($adherent, $pass);
        }
        else
        {
            return false;
        }
    }


    public function authPassword(adherent $adherent, string $pass)
    {
        $hash = $adherent->getPassword();
        if ($hash != NULL)
        {
            $hash = $adherent->getPassword();
            return password_verify($pass, $hash);
        }
        else
        {
            return false;
        }
    }

    public function authRole(adherent $adherent, int $minrole)
    {
        $ref_adherent = $this->getAdherentFromID($adherent->getID()); // avoir un adherent le plus à jour
        if (hash_equals($ref_adherent->getPassword(), $adherent->getPassword())) // verifier pass tjrs ok
        {
            return $ref_adherent->getRole() >= $minrole;
        }
        else
        {
            return false;
        }
    }

    public function authMax(adherent $adhrent, int $minrole, string $pass)
    {
        $ref_adherent = $this->getAdherentFromID($adherent->getID()); // avoir un adherent le plus à jour
        if (password_verify($pass, $ref_adherent->getPassword()))
        {
            return $ref_adherent->getRole() >= $minrole;
        }
        else
        {
            return false;
        }
    }








    /** =======================================================================================
     * EDITION DES ADHERENTS
     * ATTENTION : Non securisé au risque de modifier des données importantes !!!
     * 
     * change = modification des valeurs sans verification
     * edit = verification des valeurs et change
     */


    public function updateAdherent(adherent $adherent)
    {
        if ($this->getAdherent($adherent->getIDA()))
        {
            $req = $this->_bdd->registre()->prepare('UPDATE adherents
            SET Nom = :Nom, Prenom = :Prenom, Classe = :Classe, 
            Email = :Email, Status = :Status, 
            Role = :Role
            WHERE IDA = :IDA');
            $res = $req->execute([
                'IDA' => $adherent->getIDA(),
                'Nom' => $adherent->getNom(),
                'Prenom' => $adherent->getPrenom(),
                'Classe' => $adherent->getClasse(),
                'Status' => $adherent->getStatus(),
                'Role' => $adherent->getRole(),
                'Email' => $adherent->getEmail(),
            ]);
            return $res;
        }
        else
        {
            return err::e(err::NEXISTE_PAS);
        }
    }




    private function changePass(int $IDA, string $pass)
    {     
        $req = $this->_bdd->registre()->prepare('UPDATE adherents SET Password = :Password WHERE IDA = :IDA');
        $req->execute([
            'Password' => password_hash($pass, PASSWORD_DEFAULT),
            'IDA' => $IDA
        ]);

        return $this->getAdherent($IDA);
    }


    private function changePassHash(int $IDA, string $hash)
    {     
        $req = $this->_bdd->registre()->prepare('UPDATE adherents SET Password = :Password WHERE IDA = :IDA');
        $req->execute([
            'Password' => $hash,
            'IDA' => $IDA
        ]);

        return $this->getAdherent($IDA);
    }

    public function resetPass(int $IDA)
    {
        $adherent = $this->getAdherent($IDA);
        if ($adherent)
        {
            $req = $this->_bdd->registre()->prepare('UPDATE adherents SET Password = :Password WHERE IDA = :IDA');
            $req->execute([
                'Password' => NULL,
                'IDA' => $IDA
            ]);

            $this->deleteAllConnectionCookie($IDA);

            
            return $this->getAdherent($IDA);
        }
        else
        {
            return err::e(NEXISTE_PAS);
        }
    }

    // renvoi class:adherent
    // 
    // erreurs possibles:
    // BFORM_PASS
    // WRONG_PASS
    // BDD_PASS
    public function editPass(adherent $adherent, string $curentpass, string $newpass)
    {
        if ($this->authPassword($adherent, $curentpass))
        {
            if ($this->verifyPassForm($pass))
            {
                $adherent = $this->changePass($adherent->getIDA(), $pass);
                if ($adherent)
                {
                    return $adherent;
                }
                else
                {
                    gestion_logs::Log($_SESSION['IP'], log::TYPE_ERROR, 'gestion_adherents-editPass', 'BDD');
                    return err::e(err::BDD_PASS);
                }
            }
            else
            {
                return err::e(err::BFORM_PASS);
            }
        }
        else
        {
            return err::e(err::WRONG_PASS);
        }
    }
    



    /**
     * -> STATUS
     * 
     * retourne class:adherent
     * ou false
     * 
     */

    // editer le status d'un adherent depuis son IDA
    // utile pour le changement massif de status (ex: cheques payés)
    public function editStatusIDA(int $IDA, int $Status)
    {
        if ($this->verifyStatus($Status))
        {
            // if ($this->getAdherent($IDA))
            // {   
                $req = $this->_bdd->registre()->prepare('UPDATE adherents SET Status = :Status WHERE IDA = :IDA');
                $req->execute([
                    'Status' => $Status,
                    'IDA' => $IDA
                ]);
                
                return $this->getAdherent($IDA);
            // }
            // else
            // {
            //     return false;
            // }
        }
        else
        {
            return false;
        }
    }

    public function editStatus(adherent $adherent, int $Status)
    {
        if ($this->verifyStatus($Status))
        {
            $ID = $adherent->getID();

            if ($this->getAdherentFromID($ID))
            {
                $req = $this->_bdd->registre()->prepare('UPDATE adherents SET Status = :Status WHERE ID = :ID');
                $req->execute([
                    'Status' => $Status,
                    'ID' => $ID
                ]);

                return $this->getAdherentFromID($ID);
            }
            else
            {
                return false;
            }            
        }
        else
        {
            return false;
        }
    }





    /**
     * -> EMAIL
     * 
     * changeEmail
     * - change uniquement dans la bdd sans verification
     * - retourne un adherent au faux si erreur de bdd
     * 
     * verifyEmail
     * - verifie l'unicité et le format du mail
     * - retourne vrai ou erreur (unicité, format)
     * 
     * askEditEmail
     * - appelle verifyEmail et cree un Code de validation
     * - retourne l'erreur de verifyEmail ou vrai si mail envoyé faux sinon
     * 
     * EditEmail
     * - authentifie la demande, verifie le code de validation, verifie la forme du mail, change l'email
     * - retourne erreur (validcode invalide, pass mauvais, erreur de bdd) ou erreur de verifyMail ou l'adherent
     * 
     * Recap:
     * - erreur (unicité, format, validcode invalide, pass mauvais, erreur de bdd)
     * 1 - utiliser d'abord askEdit pour avoir verifier si le mail est joignable
     * 2 - utilise Edit pour le modifier
     * - verify email peut etre utiliser pour effectuer des verifications eventuelles
     */

    private function changeEmail(adherent $adherent, string $email)
    {
        $ID = $adherent->getID();
    
        $req = $this->_bdd->registre()->prepare('UPDATE adherents SET Email = :Email WHERE ID = :ID');
        $req->execute([
            'Email' => $email,
            'ID' => $ID
        ]);

        return $this->getAdherentFromID($ID);
    }

    public function verifyEmail(string $email)
    {
        if ($this->verifyEmailForm($email))
        {
            if (!$this->getAdherentFromMail($email)) // Verifier l'uncité du nouveau mail
            {
                return true;
            }
            else
            {
                return err::e(err::NUNI_EMAIL);
            }
        }
        else
        {
            return err::e(err::BFORM_EMAIL);;
        }
    }


    public function editEmail(adherent $adherent, string $email, int $code, string $pass)
    {
        if ($this->authPassword($pass))
        {
            $verif = $this->verifyEmail($adherent, $email);
            if (err::c($verif))
            {
                $adherent = $this->changeEmail($adherent, $email);
                if ($adherent)
                {
                    return $adherent;
                }
                else
                {
                    gestion_logs::Log($_SESSION['IP'], log::TYPE_ERROR, 'gestion_adherents-editEmail', 'BDD');
                    return err::e(err::BDD_CHANGEMAIL);
                }
            }
            else
            {
                return $verif;
            }
        }
        else
        {
            return err::e(err::WRONG_PASS);
        }
    }

    



    public function delAdherent(int $IDA)
    {
        $adherent = $this->getAdherent($IDA);
        if ($adherent)
        {
            $this->_gestion_mails->mail_Annulation($adherent);
            
            $req = $this->_bdd->registre()->prepare('DELETE FROM adherents WHERE IDA = :IDA');
            $res = $req->execute([
                'IDA' => $IDA
            ]);

            return $res;
        }
    }









    /** =======================================================================================
     * VERIFICATIONS
     */
    public function verifyPassForm(string $pass)
    {
        return preg_match("#^([0-9A-Za-z-_.,;@!?\/\\%\#*]){6,32}$#", $pass);
    }

    public function verifyEmailForm(string $email)
    {
        return preg_match("/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/", $email);
    }

    public function verifyStatus(int $Status)
    {
        if ($Status == adherent::STATUS_ADHERENT || $Status == adherent::STATUS_ANNULE || $Status == adherent::STATUS_INSCRIT)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function verifyRole(int $Role)
    {
        if ($Role == adherent::ROLE_ADHERENT || $Role == adherent::ROLE_CA || $Role == adherent::ROLE_BUREAU || $Role == adherent::ROLE_ADMIN)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // PNom correspond au prenom ou au nom -> on applique la même verification
    public function verifyPNom(string $PNom)
    {
        return preg_match("/^([A-Za-z-_ àéèëêïîç]){3,30}$/", $PNom);
    }

    public function verifyClasse($classe)
    {
        $all_classes = $this->_settings['classes'];
        return in_array($classe, $all_classes);
    }



    public function verifyAdherent(adherent $adherent)
    {
        if (!$this->verifyEmailForm($adherent->getEmail()))
        {
            return err::e(err::BFORM_EMAIL);
        }
        else if (!$this->verifyStatus($adherent->getStatus()))
        {
            return err::e(err::BFORM_STATUS);
        }
        else if (!$this->verifyRole($adherent->getRole()))
        {
            return err::e(err::BFORM_ROLE);
        }
        else if (!$this->verifyPNom($adherent->getNom()))
        {
            return err::e(err::BFORM_NOM);
        }
        else if (!$this->verifyPNom($adherent->getPrenom()))
        {
            return err::e(err::BFORM_PRENOM);
        }
        else if (!$this->verifyClasse($adherent->getClasse()))
        {
            return err::e(err::BFORM_CLASSE);
        }
        else
        {
            return true;
        }
    }




    public function getStatus()
    {
        if ($this->isRegisteredORConnected())
        {
            $adherent = $this->getAdherentFromID($_SESSION['Adherent']->getID());
            if ($adherent)
            {
                return $adherent->getStatus();
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public function checkPaiement()
    {
        $status = $this->getStatus();
        if ($status)
        {
            return $status == adherent::STATUS_ADHERENT;
        }
        else
        {
            return false;
        }
    }

    public function checkPaiementAdherent(adherent $adherent)
    {
        return $adherent->getStatus() == adherent::STATUS_ADHERENT;
    }

    // public function getStatusFromIDA(int $IDA)
    // {
    //     $adherent = $this->getAdherent($IDA);
    //     if ($adherent)
    //     {
    //         return $adherent->getStatus();
    //     }
    //     else
    //     {
    //         return false;
    //     }
    // }

    public function GotAPass(int $IDA = 0)
    {
        if ($IDA == 0)
        {
            if ($this->isConnected())
            {
                $IDA = $_SESSION['Adherent']->getIDA();
            }
        }

        if ($IDA != 0)
        {
            $adherent = $this->getAdherent($IDA);
            if ($adherent)
            {
                return $adherent->getPassword() != NULL;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }



    /** =======================================================================================
     * AUTRES
     */
    public function CountAdherents()
    {
        $req = $this->_bdd->registre()->query('SELECT COUNT(ID) FROM adherents');
        $req = $req->fetch();
        return $req[0];
    }

    public static function publicationAdhesion()
    {
        $settings = settings::p('adhesion');

        $actif = (bool) $settings['actif'];

        $today = new DateTime();
        $datedebut = new DateTime((string) $settings['date_debut']);
        $datefin  = new DateTime((string) $settings['date_fin']);

        if ($today->getTimestamp() >= $datedebut->getTimestamp() && $today->getTimestamp() <= $datefin->getTimestamp())
        {
            $datecheck = true;
        }
        else
        {
            $datecheck = false;
        }


        return $actif && $datecheck;
    }

    public function getPromoCodes()
    {
        $req = $this->_bdd->registre()->prepare('SELECT * FROM code_promo');
        $req->execute();
        $req = $req->fetchAll(PDO::FETCH_ASSOC);

        $today = new DateTime();


        $valid_codes = [];
        foreach($req as $code)
        {
            $ValidDate = unserialize($code["ValidDate"]);
            $datedebut = new DateTime((string) $ValidDate['Start']);
            $datefin  = new DateTime((string) $ValidDate['Stop']);

            if ($today->getTimestamp() >= $datedebut->getTimestamp() && $today->getTimestamp() <= $datefin->getTimestamp() && $code["Actif"])
            {
                $valid_codes[] = $code;
            }
        }

        return $valid_codes;
    }

    public function privateAdhesionToken($token)
    {
        $req = $this->_bdd->registre()->prepare('SELECT * FROM adhesion_private_token WHERE Token = :Token');
        $req->execute(['Token' => $token]);
        $req = $req->fetch(PDO::FETCH_ASSOC);
        if ($req)
        {
            if ($req["IDA"] == NULL)
            {
                $req = $this->_bdd->registre()->prepare('UPDATE adhesion_private_token SET LastUse_TimeStamp = NOW() WHERE Token = :Token');
                $req->execute(['Token' => $token]);
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public function confirm_privateAdhesionToken($token, $IDA)
    {
        $req = $this->_bdd->registre()->prepare('UPDATE adhesion_private_token SET LastUse_TimeStamp = NOW(), IDA = :IDA WHERE Token = :Token');
        $req->execute(['Token' => $token, "IDA" => $IDA]);
    }

    public function create_privateAdhesionToken()
    {
        $token = self::createKey();
        $req = $this->_bdd->registre()->prepare('INSERT INTO adhesion_private_token (Token) VALUES(:Token)');
        $res = $req->execute([
            'Token' => $token
        ]);
        if ($res)
        {
            return $token;
        }
        else
        {
            return false;
        }
    }

    /** ========================================================================================
     * KEYS
     */

    public static function createKey($length = 64)
    {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }

    // MAIL_AUTH

    private static function createCode($length = 6)
    {
        $returnString = mt_rand(1, 9);
        while (strlen($returnString) < $length)
        {
            $returnString .= mt_rand(0, 9);
        }
        return $returnString;
    }

    public function genKey($IDA, $gen_code = false, $length = 64, $pass = NULL)
    {
        $key = self::createKey($length);
        if ($gen_code)
        {
            $code = $this->createCode();
        }
        else
        {
            $code = NULL;
        }

        $req = $this->_bdd->registre()->prepare('DELETE FROM mail_auth WHERE IDA = :IDA');
        $res = $req->execute(['IDA' => $IDA]);

        $req = $this->_bdd->registre()->prepare('INSERT INTO mail_auth (IDA, QueryKey, Password, AuthCode) VALUES(:IDA, :QueryKey, :Password, :AuthCode)');
        $res = $req->execute([
            'IDA' => $IDA,
            'QueryKey' => $key,
            'Password' => $pass,
            'AuthCode' => $code
        ]);
        if ($res)
        {
            return ['key' => $key, 'code' => $code];
        }
        else
        {
            return false;
        }
    }

    public function verifCode(int $IDA, string $code)
    {
        $mail_auth = $this->getMail_Auth($IDA);
        if ($mail_auth)
        {
            return $mail_auth['AuthCode'] == $code;
        }
        else
        {
            return false;
        }
    }


    public function verifKey(int $IDA, string $key, string $code = '', $max_minutes = 5)
    {
        $mail_auth = $this->getMail_Auth($IDA);
        if ($mail_auth)
        {
            $req = $this->_bdd->registre()->prepare('DELETE FROM mail_auth WHERE IDA = :IDA');
            $res = $req->execute(['IDA' => $IDA]);

            if ($key != '' && $mail_auth['QueryKey'] == $key)
            {
                $correct_query = true;
            }
            else if ($code != '' && $mail_auth['AuthCode'] == $code)
            {
                $correct_query = true;
            }
            else 
            {
                $correct_query = false;
            }

            if ($correct_query)
            {
                $query_date = new DateTime($mail_auth['TimeStamp']);
                $today = new DateTime();

                $diff = $today->diff($query_date);

                $minutes = $diff->i + ($diff->h*60) + ($diff->d*3600);


                if ($minutes <= $max_minutes)
                {
                    return $mail_auth;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public function getMail_Auth($IDA)
    {
        $req = $this->_bdd->registre()->prepare('SELECT * FROM mail_auth WHERE IDA = :IDA');
        $req->execute(['IDA' => $IDA]);
        $req = $req->fetch(PDO::FETCH_ASSOC);
        if ($req)
        {
            return $req;
        }
        else
        {
            return false;
        }
    }


    // AUTH_KEY API

    public function auth_key_askKey(int $IDA, string $pass)
    {
        if ($this->authPasswordByIDA($IDA, $pass))
        {
            if ($this->authRole($this->getAdherent($IDA), settings::p('panel_admin')['minRole']['isRegisteredForEvent']))
            {
                return $this->auth_key_genKey($IDA);
            }
            else
            {
                return err::e(err::ADMIN_FCNFORBID);
            }
        }
        else
        {
            return err::e(err::WRONG_PASS);
        }
    }

    public function auth_key_genKey(int $IDA)
    {
        $key = self::createKey();

        $req = $this->_bdd->registre()->prepare('DELETE FROM auth_key WHERE IDA = :IDA');
        $res = $req->execute(['IDA' => $IDA]);

        $req = $this->_bdd->registre()->prepare('INSERT INTO auth_key (IDA, auth_key) VALUES(:IDA, :auth_key)');
        $res = $req->execute([
            'IDA' => $IDA,
            'auth_key' => $key
        ]);
        if ($res)
        {
            return $key;
        }
        else
        {
            return err::e(err::BDD_ERROR);;
        }
    }

    public function auth_key_verifKey(string $key)
    {
        $auth_key = $this->auth_key_getKey($key);
        if ($auth_key)
        {
            if ($key != '' && $auth_key['auth_key'] == $key)
            {
                $query_date = new DateTime($auth_key['TimeStamp']);
                $today = new DateTime();

                $diff = $today->diff($query_date);

                $heures = ($diff->h*60) + ($diff->d*3600);


                if ($heures <= 5)
                {
                    return $auth_key;
                }
                else
                {
                    $req = $this->_bdd->registre()->prepare('DELETE FROM auth_key WHERE auth_key = :auth_key');
                    $res = $req->execute(['auth_key' => $key]);
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    private function auth_key_getKey(string $key)
    {
        $req = $this->_bdd->registre()->prepare('SELECT * FROM auth_key WHERE auth_key = :auth_key');
        $req->execute(['auth_key' => $key]);
        $req = $req->fetch(PDO::FETCH_ASSOC);
        if ($req)
        {
            return $req;
        }
        else
        {
            return false;
        }
    }



}

?>