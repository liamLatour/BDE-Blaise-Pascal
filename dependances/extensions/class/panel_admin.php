<?php

class panel_admin
{

    private $_bdd,
            $_gestion_adherents,
            $_gestion_articles,
            $_gestion_mails,
            $_settings;

    const   VERIF_ONLY                  = true, 

            SEARCH_NOM                  = 1,
            SEARCH_STATUS               = 2,
            SEARCH_ROLE                 = 3,
            SEARCH_CLASSE               = 4,
            SEARCH_IDA                  = 5,



            
            FCN_ISADHERENT              = 11,
            FCN_ISREGISTEREDFOREVENT    = 12,

            FCN_VIEWADHERENTS           = 21,
            FCN_VIEWIDA                 = 22,
            FCN_EXPORTADH               = 23,

            FCN_EDITADHERENTINFOS       = 31,
            FCN_EDITADHERENTSTATUS      = 32,
            FCN_EDITADHERENTROLE        = 33,
            FCN_RESETADHERENTPASS       = 34,
            FCN_UPDATESTATUSPAIEMENT    = 35,

            FCN_CREATEARTICLE           = 41,
            FCN_EDITARTICLE             = 42,
            FCN_REMOVEARTICLE           = 43,
            FCN_ARTICLELIST             = 44,

            FCN_NOTIFYADHERENT          = 51,
            FCN_CUSTOMNOTIFADHERENT     = 52,

            FCN_DELETEADHERENT          = 61,
            FCN_CLEARREGISTRE           = 62,

            FCN_LOGS                    = 71,

            FCN_EDITSETTINGS            = 81,

            FCN_DEV_CONSOLE             = 99,



            ALLFUNCTIONS                = [
                self::FCN_ISADHERENT,
                self::FCN_ISREGISTEREDFOREVENT,

                self::FCN_VIEWADHERENTS,
                self::FCN_VIEWIDA,
                self::FCN_EXPORTADH,

                self::FCN_EDITADHERENTINFOS,
                self::FCN_EDITADHERENTSTATUS,
                self::FCN_EDITADHERENTROLE,
                self::FCN_RESETADHERENTPASS,
                self::FCN_UPDATESTATUSPAIEMENT,

                self::FCN_CREATEARTICLE,
                self::FCN_EDITARTICLE,
                self::FCN_REMOVEARTICLE,
                self::FCN_ARTICLELIST,

                self::FCN_NOTIFYADHERENT,
                self::FCN_CUSTOMNOTIFADHERENT,

                self::FCN_DELETEADHERENT,
                self::FCN_CLEARREGISTRE,

                self::FCN_EDITSETTINGS,

                self::FCN_LOGS,

                self::FCN_DEV_CONSOLE
            ];



    public function __construct(bdd $bdd, gestion_adherents $gestion_adherents, gestion_articles $gestion_articles, gestion_mails $gestion_mails)
    {
        $this->_bdd = $bdd;
        $this->_gestion_adherents = $gestion_adherents;
        $this->_gestion_articles = $gestion_articles;
        $this->_gestion_mails = $gestion_mails;
        $this->_settings = settings::p('panel_admin');
    }


    /**
     * AFFICHAGE DU PANEL ET AUTORISATION D'UTILISATION DE FONCTIONS
     */
    public function functionAllowed(int $function)
    {
        if ($this->_gestion_adherents->isConnected())
        {
            if ($this->isAFunction($function))
            {
                $adherent = $_SESSION['Adherent'];
                switch ($function)
                {              
                    case $function == self::FCN_ISADHERENT:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['isAdherent']);
                        break;
                    case $function == self::FCN_ISREGISTEREDFOREVENT:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['isRegisteredForEvent']);
                        break;

                    case $function == self::FCN_VIEWADHERENTS:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['viewAdherents']);
                        break;
                    case $function == self::FCN_VIEWIDA:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['viewIDA']);
                        break;
                    case $function == self::FCN_EXPORTADH:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['exportAdh']);
                        break;        


                    case $function == self::FCN_EDITADHERENTINFOS:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['editAdherentInfos']);
                        break;
                    case $function == self::FCN_EDITADHERENTSTATUS:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['editAdherentStatus']);
                        break;
                    case $function == self::FCN_EDITADHERENTROLE:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['editAdherentRole']);
                        break;
                    case $function == self::FCN_RESETADHERENTPASS:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['resetAdherentPass']);
                        break;
                    case $function == self::FCN_UPDATESTATUSPAIEMENT:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['updateStatusPaiement']);
                        break;



                    case $function == self::FCN_CREATEARTICLE:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['createArticle']);
                        break;
                    case $function == self::FCN_EDITARTICLE:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['editArticle']);
                        break;
                    case $function == self::FCN_REMOVEARTICLE:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['removeArticle']);
                        break;
                    case $function == self::FCN_ARTICLELIST:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['articleList']);
                        break;




                    case $function == self::FCN_NOTIFYADHERENT:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['notifyAdherent']);
                        break;
                    case $function == self::FCN_CUSTOMNOTIFADHERENT:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['customNotifAdherent']);
                        break;




                    case $function == self::FCN_DELETEADHERENT:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['deleteAdherent']);
                        break;
                    case $function == self::FCN_CLEARREGISTRE:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['clearRegistre']);
                        break;




                    case $function == self::FCN_EDITSETTINGS:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['editSettings']);
                        break;




                    case $function == self::FCN_LOGS:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['logs']);
                        break;

                    case $function == self::FCN_DEV_CONSOLE:
                        return $this->_gestion_adherents->authRole($adherent, $this->_settings['minRole']['dev_console']);
                        break;




                    default:
                        return false;
                        break;
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

    private function isAFunction(int $function)
    {
        return in_array($function, self::ALLFUNCTIONS);
        //return method_exists($this, $function);
    }

    /**
     * EVENEMENTS
     */
    public function isAdherent(int $IDA)
    {
        if ($this->functionAllowed(self::FCN_ISADHERENT))
        {
            return $this->_gestion_adherents->getAdherent($IDA);
        }
        else
        {
            return err::e(err::ADMIN_FCNFORBID);
        }
    }

    public function isAdherentNomPrenom(string $nom, string $prenom)
    {
        if ($this->functionAllowed(self::FCN_ISADHERENT))
        {
            return $this->_gestion_adherents->getAdherent($nom, $prenom);
        }
        else
        {
            return err::e(err::ADMIN_FCNFORBID);
        }
    }

    public function isRegisteredForEvent(int $articleID, string $nom, string $prenom)
    {

    }

    public function getLastInscrits()
    {
        $req = $this->_bdd->registre()->query('SELECT * FROM events ORDER BY TimeStamp LIMIT 5');
        $req = $req->fetchAll(PDO::FETCH_ASSOC);
        return $req;
    }


    /**
     * GESTION DES ADHERENTS
     */


    /**
     * RECHERCHE
     */
    public function getAdherentsByStatus(int $status)
    {
        $req = $this->_bdd->registre()->prepare('SELECT * FROM adherents WHERE Status = :Status');
        $req->execute([
            'Status' => $status
        ]);
        $req = $req->fetchAll(PDO::FETCH_ASSOC);

        return $req;
        
    }

    private function getAdherentsByRole(int $role)
    {
        $req = $this->_bdd->registre()->prepare('SELECT * FROM adherents WHERE Role = :Role');
        $req->execute([
            'Role' => $role
        ]);
        $req = $req->fetchAll(PDO::FETCH_ASSOC);

        return $req;
    }

    private function getAdherentsByNom(string $nom)
    {
        $req = $this->_bdd->registre()->prepare("SELECT * FROM adherents WHERE Nom LIKE :Nom ");
        $req->execute([
            'Nom' => "%".$nom."%"
        ]);
        $req = $req->fetchAll(PDO::FETCH_ASSOC);

        return $req;
    }

    private function getAdherentsByIDA(int $ida)
    {
        $req = $this->_bdd->registre()->prepare('SELECT * FROM adherents WHERE IDA = :IDA ');
        $req->execute([
            'IDA' => $ida
        ]);
        $req = $req->fetchAll(PDO::FETCH_ASSOC);

        return $req;
    }

    private function getAdherentsByClasse(string $classe)
    {
        $req = $this->_bdd->registre()->prepare('SELECT * FROM adherents WHERE Classe = :Classe');
        $req->execute([
            'Classe' => $classe
        ]);
        $req = $req->fetchAll(PDO::FETCH_ASSOC);

        return $req;
    }

    public function searchAdherent($info, $value)
    {
        if ($this->functionAllowed(self::FCN_VIEWADHERENTS))
        {
            switch ($info)
            {
                case $info == self::SEARCH_NOM:
                    return $this->getAdherentsByNom($value);
                    break;

                case $info == self::SEARCH_IDA:
                    return $this->getAdherentsByIDA($value);
                    break;

                case $info == self::SEARCH_CLASSE:
                    return $this->getAdherentsByClasse($value);
                    break;

                case $info == self::SEARCH_STATUS:
                    return $this->getAdherentsByStatus($value);
                    break;

                case $info == self::SEARCH_ROLE:
                    return $this->getAdherentsByRole($value);
                    break;

                default:
                    return err::e(err::WRONG_INFOS);
                    break;
            }
        }
        else
        {
            return err::e(err::ADMIN_FCNFORBID);
        }
    }



    public function newSearchAdherents(array $query_array)
    {
        if ($this->functionAllowed(self::FCN_VIEWADHERENTS))
        {
            if (sizeof($query_array) > 0)
            {
                $i = 0;
                $query_line = '';
                $query_values = [];
                foreach($query_array as $key => $value)
                {
                    if ($i == 0)
                    {
                        $query_line .= " ".$key." LIKE :".$key." ";
                    }
                    else
                    {
                        $query_line .= " AND ".$key." LIKE :".$key." ";
                    }

                    $query_values[$key] = "%".$value."%"; 

                    $i++;
                }
                $req = $this->_bdd->registre()->prepare('SELECT * FROM adherents WHERE '.$query_line.' ORDER BY Classe, Nom, Prenom, IDA');
                $req->execute($query_values);
                $req = $req->fetchAll(PDO::FETCH_ASSOC);

                return $req;

            }
        }
        else
        {
            return err::e(err::ADMIN_FCNFORBID);
        } 
        
    }






    public function getAllAdherents()
    {
        if ($this->functionAllowed(self::FCN_VIEWADHERENTS))
        {
            $req = $this->_bdd->registre()->prepare('SELECT * FROM adherents');
            $req->execute();
            $req = $req->fetchAll(PDO::FETCH_ASSOC);


            $allAdherents = [];
            foreach ($req as $one)
            {
                $allAdherents[] = $this->_gestion_adherents->createAdherentFromReq($one);
            }
            return $allAdherents;
        }
        else
        {
            return err::e(ADMIN_FCNFORBID);
        }
    }


    public function exportAdherents($results)
    {
        if ($this->functionAllowed(self::FCN_EXPORTADH))
        {
            $filepath = __DIR__.'/../../domains/docs/documents/export_adherents.csv';
            $csv = fopen($filepath, 'w+');
            fputcsv($csv, [
                'IDA',
                'Nom',
                'Prénom',
                'Classe',
                'Status',
                'Role',
                'Adresse mail',
                'Newsletter'
            ]);
            foreach($results as $res)
            {
                if ($this->functionAllowed(self::FCN_VIEWIDA))
                {
                    $IDA = $res['IDA'];
                }
                else
                {
                    $IDA = '0000';
                }
                $Nom = $res['Nom'];
                $Prenom = $res['Prenom'];
                $Classe = $res['Classe'];
                $Status = adherent::getStatusShortStringFromInt($res['Status']);
                $Role = adherent::getRoleShortStringFromInt($res['Role']);
                $Email = $res['Email'];
                $Newsletter = adherent::getNewsletterShortStringFromInt($res['Newsletter']);

                fputcsv($csv, [
                    $IDA, $Nom, $Prenom, $Classe, $Status, $Role, $Email, $Newsletter
                ]);
            }
            fclose($csv);
            $export = file_get_contents($filepath);
            unlink($filepath);

            header("Content-type: application/octet-stream");
            header("Content-disposition: attachment;filename=export_adherents.csv");
            echo $export;
        }
        else
        {
            return err::e(ADMIN_FCNFORBID);
        }
    }


    /**
     * EDITION
     */
    public function editAdherentStatus(int $IDA, int $status)
    {
        if ($this->functionAllowed(self::FCN_EDITADHERENTSTATUS))
        {
            if ($this->_gestion_adherents->editStatusIDA($ID, $status) === false)
            {
                return err::e(NEXISTE_PAS);
            }
            else
            {
                return true;
            }
        }
        else
        {
            return err::e(ADMIN_FCNFORBID);
        }
    }

    public function editAdherentRole(int $IDA, int $role)
    {
        if ($this->functionAllowed(self::FCN_EDITADHERENTROLE))
        {
            if ($this->_gestion_adherents->editRoleIDA($ID, $status) === false)
            {
                return err::e(NEXISTE_PAS);
            }
            else
            {
                return true;
            }
        }
        else
        {
            return err::e(ADMIN_FCNFORBID);
        }
    }

    public function cancel_non_adh()
    {
        if ($this->functionAllowed(panel_admin::FCN_UPDATESTATUSPAIEMENT))
        {
            $req_adh_inscrits = $this->getAdherentsByStatus(adherent::STATUS_INSCRIT);
            $erreurs = [];
            $done = 0;
            foreach($req_adh_inscrits as $req_adh)
            {
                $adherent = $this->_gestion_adherents->createAdherentFromReq($req_adh);
                if ($adherent)
                {
                    if ($adherent->getStatus() == adherent::STATUS_INSCRIT)
                    {
                        if ($this->_gestion_adherents->editStatusIDA((int) $adherent->getIDA(), adherent::STATUS_ANNULE))
                        {
                            $done++;
                            $this->_gestion_mails->mail_Annulation($adherent);
                        }
                        else
                        {
                            $erreurs[] = ["CANT_EDIT", $adherent->getIDA()];
                        }
                    }
                    else
                    {
                        $erreurs[] = ["WRONG_STATUS", $adherent->getIDA()];
                    }
                }
                else
                {
                    $erreurs[] = ["CANT_BUILD_ADH", json_encode($req_adh)];
                }
            }
            return ["done" => $done, "total" => sizeof($req_adh_inscrits), "erreurs" => $erreurs];
        }
        else
        {
            return err::e(ADMIN_FCNFORBID);
        }
    }

    public function genEditAdherent(array $values)
    {
        $adherent = $this->_gestion_adherents->getAdherent($values['Edit']);

        if ($adherent)
        {
            if ($this->functionAllowed(self::FCN_EDITADHERENTINFOS) && isset($values['Nom']) && isset($values['Prenom']) && isset($values['Classe']) && isset($values['Email']))
            {
                $adherent->setNom($values['Nom']);
                $adherent->setPrenom($values['Prenom']);
                $adherent->setClasse($values['Classe']);
                $adherent->setEmail($values['Email']);
            }

            if ($this->functionAllowed(self::FCN_EDITADHERENTROLE) && isset($values['Role']))
            {
                $adherent->setRole($values['Role']);
            }
            
            if ($this->functionAllowed(self::FCN_EDITADHERENTSTATUS) && isset($values['Status']))
            {
                $adherent->setStatus($values['Status']);
            }

            return $adherent;
        }
        else
        {
            return false;
        }
    }

    public function resetAdherentPass(int $IDA)
    {
        if ($this->functionAllowed(self::FCN_RESETADHERENTPASS))
        {
            return $this->_gestion_adherents->resetPass($IDA);
        }
        else
        {
            return err::e(ADMIN_FCNFORBID);
        }
    }


    // Notification
    public function notifyAdherent(int $IDA, int $notif)
    {

    }

    public function customNotifAdherent(int $IDA, string $notif)
    {

    }

    // Suppression des adherents
    public function deleteAdherent(int $IDA)
    {
        if ($this->functionAllowed(self::FCN_DELETEADHERENT))
        {
            $adherent = $this->_gestion_adherents->getAdherent($IDA);
            if ($adherent)
            {
                gestion_logs::Log($_SESSION['IP'], log::TYPE_ADMIN, 'adherents/delete', $adherent->getArray());
                $this->_gestion_adherents->delAdherent($IDA);
                return true;
            }
            else
            {
                return err::e(NEXISTE_PAS);
            }
        }
        else
        {
            return err::e(ADMIN_FCNFORBID);
        }
    }

    private function clearRegistre(int $key)
    {

    }

    public function askClearRegistre()
    {

    }

    /** 
     * ARTICLES
     * (s'occupe de gérer les articles : authentification seulement)
     */
    public function createArticle(array $post)
    {
        if ($this->functionAllowed(self::FCN_CREATEARTICLE))
        {
            return $this->_gestion_articles->createArticleHandler($post);
        }
        else
        {
            return err::e(ADMIN_FCNFORBID);
        }
    }

    public function editArticle(array $post)
    {

    }

    public function removeArticle(int $ID)
    {

    }


    /**
     * PARAMETRES
     */

    public function editSetting(string $setting, $value)
    {
        if ($this->functionAllowed(self::FCN_EDITSETTINGS))
        {
            if (settings::edit($setting, $value) === false)
            {
                gestion_logs::Log($_SESSION['IP'], log::TYPE_ERROR, 'parametres/edit', 'ADMIN_JSONWRITE');
                return err::e(err::ADMIN_JSONWRITE);
            }
            else
            {
                gestion_logs::Log($_SESSION['IP'], log::TYPE_ADMIN, 'parametres/edit', [$setting, settings::p($setting)]);
                return true;
            }
        }
        else
        {
            return err::e(err::ADMIN_FCNFORBID);
        }
    }

    public function verifSetting(string $setting, $value)
    {
        // json_decode retourne false si il y a un problème donc il faut dissocier false du reste
        if ($value == 'false')
        {
            $value = false;
            $test = true;
        }
        else
        {
            $value = json_decode($value, true);
            $test = $value;
        }

        // gestion d'une erreur de json
        if ($test === false || $test == NULL)
        {
            return err::e(err::ADMIN_BADJSON);

        }
        else
        {
            
            $modif['name'] = $setting;
            $modif['value'] = $value;
            self::genToken();

            $_SESSION['panel_admin']['settings']['edit'] = $modif;

            return true;
        }
    }

    public function editAdhesion(string $date_debut, string $date_fin, $actif)
    {
        $setting = [
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'actif' => $actif,
            'lien_helloasso' => settings::p('adhesion')['lien_helloasso']
        ];
        return $this->editSetting('adhesion', $setting);

    }


    /**
     * TOKEN
     */

    public static function genToken($length = 16)
    {
        $token = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
        $_SESSION['token'] = $token;
        return $token;
    }

    public static function verifToken($token)
    {
        if (isset($_SESSION['token']) && $_SESSION['token'] == $token)
        {
            unset($_SESSION['token']);
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function getToken()
    {
        if (isset($_SESSION['token']))
        {
            return $_SESSION['token'];
        }
        else
        {
            return false;
        }
    }




}




?>