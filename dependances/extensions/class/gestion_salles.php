<?php


class gestion_salles
{

   
    const   UPDATE_TRUST    = 1,
            UPDATE_POP      = 2,

            ACTION_TRUST    = 1,
            ACTION_POP      = 2,
            ACTION_ADD      = 3;
    
   
   
   
   
   
    private $_bdd,
            $_settings;





    /**
    * CONSTRUCTION
    */
    public function __construct(bdd $bdd)
    {
        $this->_settings = settings::p('gestion_salles');
        $this->_bdd = $bdd;
    }

































    /**
     * 
     * FONCTION BASIQUES
     * VERIFICATION ET TRANSFORMATION
     * 
     */



    public static function heure_int2string(int $heure)
    {
        switch($heure)
        {
            case $heure == 8: return 'huit'; break;
            case $heure == 9: return 'neuf'; break;
            case $heure == 10: return 'dix'; break;
            case $heure == 11: return 'onze'; break;
            case $heure == 12: return 'douze'; break;
            case $heure == 13: return 'treize'; break;
            case $heure == 14: return 'quatorze'; break;
            case $heure == 15: return 'quinze'; break;
            case $heure == 16: return 'seize'; break;
            case $heure == 17: return 'dixsept'; break;
            case $heure == 18: return 'dixhuit'; break;

            default: return false; break;
        }
    }

    public static function heure_string2int(string $heure)
    {
        switch($heure)
        {
            case $heure == 'huit': return 8; break;
            case $heure == 'neuf': return 9; break;
            case $heure == 'dix': return 10; break;
            case $heure == 'onze': return 11; break;
            case $heure == 'douze': return 12; break;
            case $heure == 'treize': return 13; break;
            case $heure == 'quatorze': return 14; break;
            case $heure == 'quinze': return 15; break;
            case $heure == 'seize': return 16; break;
            case $heure == 'dixsept': return 17; break;
            case $heure == 'dixhuit': return 18; break;

            default: return false; break;
        }
    }

    public static function verifHeure($heure)
    {
        if (is_string($heure))
        {    
            if (self::heure_string2int($heure) === false)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        else if (is_int($heure))
        {
            return isBetween($heure, 8, 18);
        }
    }

    public static function verifJour(string $jour)
    {
        return (
            $jour == salle::JOUR_LUNDI
            || $jour == salle::JOUR_MARDI
            || $jour == salle::JOUR_MERCREDI
            || $jour == salle::JOUR_JEUDI
            || $jour == salle::JOUR_VENDREDI
            || $jour == salle::JOUR_SAMEDI
        );
    }

    public static function verifSalle(string $salle)
    {
        return preg_match("/^[1-5]?[0-6]?[0-9]([abcdef]|bis|tris)?$/", $salle);
    }

    public static function verifReq($req)
    {
        if (is_array($req) && sizeof($req) > 0)
        {
            if ((isset($req['huit']) || is_null($req['huit'])) 
            && (isset($req['neuf']) || is_null($req['neuf'])) 
            && (isset($req['dix']) || is_null($req['dix'])) 
            && (isset($req['onze']) || is_null($req['onze'])) 
            && (isset($req['douze']) || is_null($req['douze'])) 
            && (isset($req['treize']) || is_null($req['treize'])) 
            && (isset($req['quatorze']) || is_null($req['quatorze'])) 
            && (isset($req['quinze']) || is_null($req['quinze'])) 
            && (isset($req['seize']) || is_null($req['seize'])) 
            && (isset($req['dixsept']) || is_null($req['dixsept'])) 
            && (isset($req['dixhuit']) || is_null($req['dixhuit']))
                
                && isset($req['Salle'])
                && isset($req['IDH'])
                && isset($req['Trust'])
                && isset($req['Pop'])
                && isset($req['Contributeur'])
                && isset($req['TimeStamp'])
                )
            {
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

    private static function getToutesHeuresFromReq(array $req)
    {
        if  (
           (isset($req['huit']) || is_null($req['huit'])) 
        && (isset($req['neuf']) || is_null($req['neuf'])) 
        && (isset($req['dix']) || is_null($req['dix'])) 
        && (isset($req['onze']) || is_null($req['onze'])) 
        && (isset($req['douze']) || is_null($req['douze'])) 
        && (isset($req['treize']) || is_null($req['treize'])) 
        && (isset($req['quatorze']) || is_null($req['quatorze'])) 
        && (isset($req['quinze']) || is_null($req['quinze'])) 
        && (isset($req['seize']) || is_null($req['seize'])) 
        && (isset($req['dixsept']) || is_null($req['dixsept'])) 
        && (isset($req['dixhuit']) || is_null($req['dixhuit']))
            )
        {
            return  [
                8 => $req['huit'],
                9 => $req['neuf'],
                10 => $req['dix'],
                11 => $req['onze'],
                12 => $req['douze'],
                13 => $req['treize'],
                14 => $req['quatorze'],
                15 => $req['quinze'],
                16 => $req['seize'],
                17 => $req['dixsept'],
                18 => $req['dixhuit']
            ];
        }
        else
        {
            return false;
        }
    }


    public static function getPlage(int $centre, array $toutesheures)
    {
        $min_heure = $centre;
        $max_heure = $centre;

        $i = $centre;
        while ($i >= 8 && isset($toutesheures[$i]) && $toutesheures[$i] != NULL)
        {
            $min_heure = $i;
            $i--;
        }

        $i = $centre;
        while ($i <= 18 && isset($toutesheures[$i]) && $toutesheures[$i] != NULL)
        {
            $max_heure = $i;
            $i++;
        }

        return ['debut' => $min_heure, 'fin' => $max_heure];
    }

    public static function getCurrentHeure()
    {
        $heure = (int) date('G');
        $minute = (int) date('i');
        if ($minute >= 50)
        {
            return $heure + 1;
        }
        else
        {
            return $heure;
        }
    }

    public static function getCurrentJour()
    {
        $jour = (int) date('N');
        switch ($jour)
        {
            case $jour == 1:
                return salle::JOUR_LUNDI;
                break;
            case $jour == 2:
                return salle::JOUR_MARDI;
                break;
            case $jour == 3:
                return salle::JOUR_MERCREDI;
                break;
            case $jour == 4:
                return salle::JOUR_JEUDI;
                break;
            case $jour == 5:
                return salle::JOUR_VENDREDI;
                break;
            case $jour == 6:
                return salle::JOUR_SAMEDI;
                break;
            default:
                return false;
                break;
        }
    }

    public function getJoke()
    {
        $len = sizeof($this->_settings['jokes']);
        $i = random_int(0, $len - 1);
        return $this->_settings['jokes'][$i];
    }

    // public static function XP2LVL(int $xp)
    // {
    //     if (isBetween($xp, 0, 10))
    //     {
    //         $lvl = ($xp) / 10;
    //     }
    //     else if (isBetween($xp, 10, 30))
    //     {
    //         $lvl = 1 + ($xp - 10) / 20;
    //     }
    //     else if (isBetween($xp, 30, 60))
    //     {
    //         $lvl = 2 + ($xp - 30) / 30;
    //     }
    //     else if (isBetween($xp, 60, 120))
    //     {
    //         $lvl = 3 + ($xp - 60) / 60;
    //     }
    //     else if (isBetween($xp, 120, 200))
    //     {
    //         $lvl = 4 + ($xp - 120) / 120;
    //     }
    //     else if ($xp > 200)
    //     {
    //         $lvl = 999;
    //     }
    //     else 
    //     {
    //         $lvl = 0;
    //     }
    //     return $lvl;
    // }

    public static function XP2LVL(int $xp)
    {
        return 1.76*log(0.072*$xp + 1);
    }

    public static function LVL2Trust(float $lvl)
    {
        if ($lvl < 0.5)
        {
            $trust = 1;
        }
        else if ($lvl < 1)
        {
            $trust = 2;
        }
        else if ($lvl < 1.5)
        {
            $trust = 5;
        }
        else if ($lvl < 2)
        {
            $trust = 8;
        }
        else if ($lvl < 2.5)
        {
            $trust = 10;
        }
        else if ($lvl < 3)
        {
            $trust = 13;
        }
        else if ($lvl < 3.5)
        {
            $trust = 15;
        }
        else if ($lvl < 4)
        {
            $trust = 18;
        }
        else if ($lvl < 4.5)
        {
            $trust = 20;
        }
        else if ($lvl < 5)
        {
            $trust = 25;
        }
        else if ($lvl >= 5)
        {
            $trust = 30;
        }
        else if ($lvl == 999)
        {
            $trust = 40;
        }
        else
        {
            $trust = 1;
        }
        return $trust;
    }







































    /**
     * 
     * GESTION DES CONTRIBUTEURS
     * 
     * 
     */

    public static function genIDC($length = 64)
    {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }

    public function loadContributeur()
    {
        if (!$this->isBanIP($_SESSION['IP']))
        {
            if (isset($_COOKIE['t']))
            {
                if (isset($_COOKIE['cont']))
                {
                    if (!$this->verifIDC($_COOKIE['cont']))
                    {
                        $this->createContributeur();
                    }
                }
                else
                {
                    $this->createContributeur();
                }

                if ($this->verifBan())
                {
                    MessagePage("Vous avez été banni, vous ne pouvez plus vous servir du service.");
                    die();
                }

            }
            else
            {
                $c_page = basename($_SERVER['PHP_SELF'],'.php');
                if (!isset($_SESSION['salles']['tuto_step']) || $c_page != 'tuto')
                {
                    $_SESSION['salles']['tuto_step'] = 1;
                    header('Location: https://salles.bde-bp.fr/tuto');
                    die();
                }
            }
        }
        else
        {
            MessagePage("Vous avez été banni, vous ne pouvez plus vous servir du service.<br>
            Le bannissement est temporaire et la durée par defaut est de 7 jours.
            <hr>
            <strong>Merci de ne pas recréer un compte car cela ne changera rien et allongera seulement la durée de votre peine.</strong>");
            die();
        }
    }

    public function createContributeur()
    {
        $IDC = self::genIDC();

        $req = $this->_bdd->salleop()->prepare('INSERT INTO contributeurs (IDC, Level) VALUES(:IDC, :Level)');
        $res = $req->execute([
            'IDC' => $IDC,
            'Level' => 0
        ]);

        setcookie("cont", $IDC, time()+366*24*60*60, "/", "bde-bp.fr"); 

        $_COOKIE['cont'] = $IDC;
    }

    public function getIDC()
    {
        if (isset($_COOKIE['cont']) && $this->verifIDC($_COOKIE['cont']))
        {
            $req = $this->_bdd->salleop()->prepare('UPDATE contributeurs set LastConnect = NOW() WHERE IDC = :IDC');
            $res = $req->execute([
                'IDC' => $_COOKIE['cont']
            ]);
            
            return $_COOKIE['cont'];
        }
        else
        {
            return false;
        }
    }

    public function verifIDC(string $IDC)
    {
        $req = $this->_bdd->salleop()->prepare('SELECT * from contributeurs WHERE IDC = :IDC');
        $req->execute([
            'IDC' => $IDC
        ]);
        $res = $req->fetch(PDO::FETCH_ASSOC);
        return (bool) $res;
    }

    // =========================================================================
    // MODERATION
    public function verifBan() // Renvoi true si bani
    {
        $IDC = $this->getIDC();
        $IP = $_SESSION['IP'];
        
        if ($IDC === false)
        {
            return $this->isBanIP($IP);
        }
        else
        {
            return $this->isBan($IDC) || $this->isBanIP($IP);
        }
    }


    public function isBan(string $IDC)
    {
        $req = $this->_bdd->salleop()->prepare('SELECT * from moderation WHERE IDC = :IDC');
        $req->execute([
            'IDC' => $IDC
        ]);
        $res = $req->fetch(PDO::FETCH_ASSOC);
        if ($res)
        {
            return $this->banCheckFromReq($res);
        }
        else
        {
            return false;
        }
        
    }

    public function isBanIP(string $IP)
    {
        $req = $this->_bdd->salleop()->prepare('SELECT * from moderation WHERE IP = :IP');
        $req->execute([
            'IP' => $IP
        ]);
        $res = $req->fetch(PDO::FETCH_ASSOC);

        if ($res)
        {
            return $this->banCheckFromReq($res);
        }
        else
        {
            return false;
        }
    }

    private function banCheckFromReq(array $req)
    {
        $timestamp = $res['TimeStamp'];
        $ban_days = $res['Days'];
        $IDC = $req['IDC'];
        $IP = $req['IP'];


        $today = new DateTime();
        $ban_date = new DateTime($timestamp);

        $diff = $today->diff($ban_date);
        $days = $diff->d;
        
        if ($days <= $ban_days)
        {
            return true;
        }
        else
        {
            $this->cancelBan($IDC, $IP);
            return false;
        }
    }

    public function cancelBan(string $IDC, string $IP)
    {
        $req = $this->_bdd->registre()->prepare('DELETE FROM moderation WHERE IDC = :IDC AND IP = :IP');
        $res = $req->execute([
            'IDC' => $IDC,
            'IP' => $IP
        ]);
        return $res;
    }

    public function ban(string $IDC, int $days = 7)
    {
        if ($this->verifIDC($IDC))
        {
            $IP = $_SESSION['IP'];

            $req = $this->_bdd->salleop()->prepare('INSERT INTO moderation (IDC, IP, Days) VALUES(:IDC, :IP, :Days)');
            $res = $req->execute([
                'IDC' => $IDC,
                'IP' => $IP,
                'Days' => $days
            ]);
            return true;
        }
        else
        {
            return false;
        }
    }


    // =========================================================================
    // XP ET LVL
    public function getXP(string $IDC)
    {
        $req = $this->_bdd->salleop()->prepare('SELECT Level from contributeurs WHERE IDC = :IDC');
        $req->execute([
            'IDC' => $IDC
        ]);
        $res = $req->fetch(PDO::FETCH_ASSOC);
        if ($res)
        {
            return $res['Level'];
        }
        else
        {
            return false;
        }
    }

    public function updateXP(string $IDC, int $xp)
    {
        if ($this->verifIDC($IDC))
        {
            $req = $this->_bdd->salleop()->prepare('UPDATE contributeurs set Level = :Level WHERE IDC = :IDC');
            $res = $req->execute([
                'IDC' => $IDC,
                'Level' => $xp
            ]);
            return $res;
        }
        else
        {
            return false;
        }
    }

    public function getLVL(string $IDC)
    {
        $xp = $this->getXP($IDC);
        if ($xp)
        {
            return self::XP2LVL((int) $xp);
        }
        else
        {
            return false;
        }
    }


    // =========================================================================
    // ACTIONS 
    public function getActions(string $IDC)
    {
        $req = $this->_bdd->salleop()->prepare('SELECT Actions from contributeurs WHERE IDC = :IDC');
        $req->execute([
            'IDC' => $IDC
        ]);
        $res = $req->fetch(PDO::FETCH_ASSOC);
        if ($res)
        {
            return unserialize($res['Actions']);
        }
        else
        {
            return false;
        }
    }

    public function canUpdate($update, string $salle, string $jour, string $heure)
    {
        $IDC = $this->getIDC();
        if ($IDC === false)
        {
            return false;
        }
        else
        {
            $actions = $this->getActions($IDC);
            if ($update == self::ACTION_TRUST)
            {
                if ($salle != '40' && $salle != '37')
                {
                    return !(isset($actions['trust'][$jour][$salle][$heure][date('z')]));
                }
                else
                {
                    return false;
                }
            }
            else if ($update == self::ACTION_POP)
            {
                return !(isset($actions['pop'][$jour][$salle][$heure][date('z')]));
            }
            else
            {
                return false;
            }
        }
    }

    public function newAction($action, string $jour = '', string $salle = '', string $heure = '')
    {
        $IDC = $this->getIDC();
        if ($IDC === false)
        {
            return err::e(err::NOT_LOGIN);
        }
        else
        {
            $level = $this->getXP($IDC);
            $actions = $this->getActions($IDC);
            if ($action == self::ACTION_ADD)
            {
                $level = $level + 5;
            }
            else if ($action == self::ACTION_TRUST)
            {
                $level = $level + 2;
                unset($actions['trust'][$jour][$salle][$heure]);
                $actions['trust'][$jour][$salle][$heure][date('z')] = true;
                $this->updateActions($IDC, $actions);
            }
            else if ($action == self::ACTION_POP)
            {
                $level = $level + 3;
                unset($actions['pop'][$jour][$salle][$heure]);
                $actions['pop'][$jour][$salle][$heure][date('z')] = true;
                $this->updateActions($IDC, $actions);
            }
            $this->updateXP($IDC, $level);
        }
    }

    public function updateActions(string $IDC, array $actions)
    {
        if ($this->verifIDC($IDC))
        {
            $actions = serialize($actions);
            
            $req = $this->_bdd->salleop()->prepare('UPDATE contributeurs set Actions = :Actions WHERE IDC = :IDC');
            $res = $req->execute([
                'IDC' => $IDC,
                'Actions' => $actions
            ]);
            return $res;
        }
        else
        {
            return false;
        }
    }



    // =========================================================================
    // RECUPERER LES BOOST PR LVL
    public function getTrustBoost()
    {
        $IDC = $this->getIDC();
        if ($IDC === false)
        {
            return err::e(err::NOT_LOGIN);
        }
        else
        {
            $lvl = $this->getLVL($IDC);
            $trust = self::LVL2Trust($lvl);

            return $trust;
        }
    }

    public function TrustToAdd($positive)
    {
        $IDC = $this->getIDC();
        if ($IDC === false)
        {
            return err::e(err::NOT_LOGIN);
        }
        else
        {
            $lvl = $this->getLVL($IDC);
            $trust = self::LVL2Trust($lvl);

            if ($positive)
            {
                return $trust;
            }
            else
            {
                return (-1)*$trust;
            }
        }
    }


    // =========================================================================
    // TUTO
    public function finishTuto()
    {
        if (isset($_SESSION['salles']['tuto_step']) && $_SESSION['salles']['tuto_step'] >= 8)
        {
            unset($_SESSION['salles']['tuto_step']);
            setcookie("t", self::genIDC(6), time()+366*24*60*60, "/", "bde-bp.fr"); 
            $_COOKIE['t'] = '';
            header('Location: https://salles.bde-bp.fr');
        }
    }
















































    /**
     * 
     * GESTION DES SALLES
     * 
     * 
     */


    private static function createSalleFromReq($req, string $heure)
    {
        if (self::verifReq($req))
        {
            return new salle([
                'IDH' => $req['IDH'],
                'Nom' => $req['Salle'], 
                'Heure' => $heure,
                'Trust' => $req['Trust'],
                'Pop' => $req['Pop'],
                'Plage' => self::getPlage(self::heure_string2int($heure), self::getToutesHeuresFromReq($req)),
                'Contributeur' => $req['Contributeur']
            ]);
        }
        else
        {
            return false;
        }

    }


   
    public function addSalle(string $salle, string $jour, string $heure)
    {
        $IDC = $this->getIDC();
        if ($IDC === false)
        {
            return err::e(err::NOT_LOGIN);
        }
        else
        {
            if (self::verifJour($jour) && self::verifHeure($heure))
            {              
                if (self::verifSalle($salle))  
                {
                    if (!$this->salleExist($salle, $jour))
                    {
                        $reqA = $this->_bdd->salleop()->prepare('INSERT INTO infos (Trust, Pop, Contributeur)
                        VALUES(:Trust, :Pop, :Contributeur)');
                        $reqA = $reqA->execute([
                            'Trust' => 100 + $this->getTrustBoost(),
                            'Pop' => 0,
                            'Contributeur' => $IDC
                        ]);


                        $IDH = $this->_bdd->salleop()->lastInsertId();
                        
                    
                        $reqB = $this->_bdd->salleop()->prepare('INSERT INTO '.$jour.' (Salle, '.$heure.') VALUES(:Salle, :IDH)');
                        $reqB = $reqB->execute([
                            'Salle' => $salle,
                            'IDH' => $IDH
                        ]);

                        $this->newAction(self::ACTION_ADD);

                        return $reqB && $reqA;
                    }
                    else if ($this->getIDH($salle, $jour, $heure) === NULL)
                    {
                        $reqA = $this->_bdd->salleop()->prepare('INSERT INTO infos (Trust, Pop, Contributeur)
                        VALUES(:Trust, :Pop, :Contributeur)');
                        $reqA = $reqA->execute([
                            'Trust' => 100 + $this->getTrustBoost(),
                            'Pop' => 0,
                            'Contributeur' => $IDC
                        ]);


                        $IDH = $this->_bdd->salleop()->lastInsertId();
                        
                    
                        $reqB = $this->_bdd->salleop()->prepare('UPDATE '.$jour.' SET '.$heure.' = :IDH WHERE Salle = :Salle');
                        $reqB = $reqB->execute([
                            'Salle' => $salle,
                            'IDH' => $IDH
                        ]);

                        $this->newAction(self::ACTION_ADD);

                        return $reqB && $reqA;
                    }
                    else
                    {
                        return err::e(err::SALLE_EXISTE);
                    }
                }
                else
                {
                    return err::e(err::BAD_SALLE);
                }
            }
            else
            {
                return err::e(err::BAD_JOURHEURE);
            }
        }
    }

    public function salleExist(string $salle, string $jour)
    {
        if ($this->verifSalle($salle) && $this->verifJour($jour))
        {
            $req = $this->_bdd->salleop()->prepare('SELECT * FROM '.$jour.' WHERE Salle = :Salle');
            $req->execute(['Salle' => $salle]);
            $res = $req->fetch(PDO::FETCH_ASSOC);
            return (bool) $res;
        }
        else
        {
            return false;
        }
    }

    public function getAllIDH(string $salle, string $jour)
    {
        if ($this->verifSalle($salle) && $this->verifJour($jour) && $this->salleExist($salle, $jour))
        {
            $req = $this->_bdd->salleop()->prepare('SELECT * FROM '.$jour.' WHERE Salle = :Salle');
            $req->execute(['Salle' => $salle]);
            $res = $req->fetch(PDO::FETCH_ASSOC);
            return $this->getToutesHeuresFromReq($res);
        }
        else
        {
            return false;
        }
    }

    public function getIDH(string $salle, string $jour, string $heure)
    {
        if ($this->verifSalle($salle) && $this->verifJour($jour) && $this->verifHeure($heure) && $this->salleExist($salle, $jour))
        {
            return $this->getAllIDH($salle, $jour)[self::heure_string2int($heure)];
        }
        else
        {
            return false;
        }
    }

    public function getSalle(string $salle, string $jour, string $heure)
    {
        if ($this->verifSalle($salle) && $this->verifJour($jour) && $this->verifHeure($heure) && $this->salleExist($salle, $jour))
        {
            $IDH = $this->getIDH($salle, $jour, $heure);
            $req = $this->_bdd->salleop()->prepare('SELECT j.*, i.IDH, i.Trust, i.Pop, i.Contributeur, i.TimeStamp
            FROM '.$jour.' j
            INNER JOIN infos i ON j.'.$heure.' = i.IDH
            WHERE i.IDH = :IDH
            ');
            $req->execute([
                'IDH' => $IDH
            ]);
            $res = $req->fetch(PDO::FETCH_ASSOC);
            return self::createSalleFromReq($res, $heure);
        }
        else
        {
            return false;
        }
    }

    public function getSalles(string $jour, string $heure)
    {
        if (self::verifJour($jour) && self::verifHeure($heure))
        {
            $req = $this->_bdd->salleop()->query('SELECT j.*, i.IDH, i.Trust, i.Pop, i.Contributeur, i.TimeStamp
            FROM '.$jour.' j
            INNER JOIN infos i ON j.'.$heure.' = i.IDH
            WHERE j.'.$heure.' IS NOT NULL
            ORDER BY i.Trust DESC, i.TimeStamp DESC
            ');
            $salles_dispo = $req->fetchAll(PDO::FETCH_ASSOC);

            if ($salles_dispo && is_array($salles_dispo) && sizeof($salles_dispo) > 0)
            {
                $salles_class = [];

                foreach($salles_dispo as $salle)
                {
                    $salles_class[] = self::createSalleFromReq($salle, $heure);
                }

                return $salles_class;
            }
            else
            {
                return err::e(err::NO_SALLES);
            }
        }
        else
        {
            return err::e(err::BAD_JOURHEURE);
        }

    }

    public function updateSalle(string $salle, string $jour, string $heure, int $action, bool $positive)
    {
        if ($this->verifSalle($salle) && $this->verifJour($jour) && $this->verifHeure($heure))
        {
            if ($this->salleExist($salle, $jour))
            {
                $obj_salle = $this->getSalle($salle, $jour, $heure);
                $trust = $obj_salle->getTrust();
                $pop = $obj_salle->getPop();
                if ($action == self::UPDATE_TRUST)
                {
                    if (($trust + $this->TrustToAdd($positive)) >= 200)
                    {
                        $trust = 200;
                    }
                    else if (($trust + $this->TrustToAdd($positive)) <= 0)
                    {
                        $trust = 0;
                    }
                    else
                    {
                        $trust = $trust + $this->TrustToAdd($positive);
                    }

                    
                    
                    $IDH = $this->getIDH($salle, $jour, $heure);
                    $req = $this->_bdd->salleop()->prepare('UPDATE infos
                    SET Trust = :Trust
                    WHERE IDH = :IDH');
                    $res = $req->execute([
                        'IDH' => $IDH,
                        'Trust' => $trust
                    ]);

                    $this->newAction(self::ACTION_TRUST, $jour, $salle, $heure);

                    return (bool) $res;
                }
                else if ($action == self::UPDATE_POP)
                {
                    if (($pop + $this->TrustToAdd($positive)) >= 100)
                    {
                        $pop = 100;
                    }
                    else if (($trust + $this->TrustToAdd($positive)) <= 0)
                    {
                        $pop = 0;
                    }
                    else
                    {
                        $pop = $pop + $this->TrustToAdd($positive);
                    }
                    
                    $IDH = $this->getIDH($salle, $jour, $heure);
                    $req = $this->_bdd->salleop()->prepare('UPDATE infos
                    SET Pop = :Pop
                    WHERE IDH = :IDH');
                    $res = $req->execute([
                        'IDH' => $IDH,
                        'Pop' => $pop + $this->TrustToAdd($positive)
                    ]);

                    $this->newAction(self::ACTION_POP, $jour, $salle, $heure);

                    return (bool) $res;
                }
                else
                {
                    return err::e(err::BAD_ACTION);
                }
            }
            else
            {
                return err::e(err::SALLE_EXISTE_PAS);
            }
        }
        else
        {
            return err::e(err::BAD_JOURHEURE);
        }        
    }

    public function deleteSalle(string $salle, string $jour, string $heure)
    {
        if ($this->verifSalle($salle)
            && $this->verifJour($jour)
            && $this->verifHeure($heure)
            && $this->salleExist($salle, $jour))
        {
            $IDH = $this->getIDH($salle, $jour, $heure);
            
            $req = $this->_bdd->salleop()->prepare('UPDATE '.$jour.' SET '.$heure.' = NULL WHERE Salle = :Salle');
            $req = $req->execute([
                'Salle' => $salle
            ]);
            
            $req = $this->_bdd->registre()->prepare('DELETE FROM infos WHERE IDH = :IDH');
            $res = $req->execute([
                'IDH' => $IDH
            ]);
        }
    }

    public function resetTrust(int $IDH)
    {
        $req = $this->_bdd->salleop()->prepare('UPDATE infos
        SET Trust = :Trust
        WHERE IDH = :IDH');
        $res = $req->execute([
            'IDH' => $IDH,
            'Trust' => 100
        ]);
    }

    






}






?>