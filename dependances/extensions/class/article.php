<?php


/**
 * 
 * 
 * 
 * INITULISE
 * 
 * 
 * 
 */


class article
{


    private $_ID,

            $_Status,

            $_Titre,
            $_Soustitre,
            $_Description,
            $_MiniatureID,

            $_DateDebut,
            $_DateFin,

            $_Contenu,

            $_FormInputs,

            $_CustomData;

    const   STATUS_ACTIF        = 1,
            STATUS_INACTIF      = 2,
    
            INPUTTYPE_CLASSIC   = 1,
            INPUTTYPE_SELECT    = 2,
            INPUTTYPE_EMAIL     = 3,
            INPUTTYPE_TEL       = 4,
            INPUTTYPE_CLASSE    = 5,
            INPUTTYPE_DATE      = 6;

    
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
    public function setStatus($value) { $this->_Status = $value; }
    public function setTitre($value) { $this->_Titre = $value; }
    public function setSoustitre($value) { $this->_Soustitre = $value; }
    public function setDescription($value) { $this->_Description = $value; }
    public function setMiniatureID($value) { $this->_MiniatureID = $value; }
    public function setDateDebut($value) { $this->_DateDebut = $value; }
    public function setDateFin($value) { $this->_DateFin = $value; }
    public function setContenu($value) { $this->_Contenu = $value; }
    public function setFormInputs($value) { $this->_FormInputs = $value; }
    public function setCustomData($value) { $this->_CustomData = $value; }

    // GETTERS
    public function getID() { return $this->_ID; }
    public function getStatus() { return $this->_Status; }
    public function getTitre() { return $this->_Titre; }
    public function getSoustitre() { return $this->_Soustitre; }
    public function getDescription() { return $this->_Description; }
    public function getMiniatureID() { return $this->_MiniatureID; }
    public function getDateDebut() { return $this->_DateFin; }
    public function getDateFin() { return $this->_DateFin; }
    public function getContenu() { return $this->_Contenu; }
    public function getFormInputs() { return $this->_FormInputs; }
    public function getCustomData() { return $this->_CustomData; }



    public function isActif()
    {
        $today = new DateTime();
        $datedebut = new DateTime($this->_DateDebut);
        $datefin  = new DateTime($this->_DateFin);

        return  $this->_Status == self::STATUS_ACTIF
                && $today->getTimestamp() >= $datedebut->getTimestamp()
                && $today->getTimestamp() <= $datefin->getTimestamp();
    }


    /**
     * 
     * FORMULAIRES POUR LES ARTICLES
     * 
     */

    // Formattage json pour CHAQUE input
    // {
    //     "Identifiant":
    //     {
    //         "Titre": "Titre",
    //         "Type": 1,
    //         "Requis": true,
    //         "Regex": "",
    //         "SelectData":
    //         {
    //             "Option_Identifiant": "Option_Valeur",
    //             "Option_Identifiant": "Option_Valeur"
    //         }
    //     },
    //     "Identifiant2":
    //     {
    //         "Titre": "Titre",
    //         "Type": 1,
    //         "Requis": true,
    //         "Regex": "",
    //         "SelectData":
    //         {
    //             "Option_Identifiant": "Option_Valeur",
    //             "Option_Identifiant": "Option_Valeur"
    //         }
    //     }
    // }

    // Pas de vigule après le dernier input !!



    public static function FormInputsFormattage(string $json)
    {
        $jsonarray = json_decode($json, true);
        if ($jsonarray == NULL || $jsonarray == false)
        {
            return [err::e(err::ARTFORM_IDORJSONFORM)];
        }
        else
        {
            if (sizeof($jsonarray) > 0)
            {
                $erreurs = [];
                foreach($jsonarray as $key => $input)
                {            
                    if (isset($input['Titre']) && isset($input['Type']) && isset($input['Requis']) && isset($input['Regex']))
                    {
                        if ($input['Type'] == self::INPUTTYPE_SELECT)
                        {
                            if (!isset($input['SelectData']) || sizeof($input['SelectData']) == 0)
                            {
                                $erreurs[] = err::e(err::ARTFORM_OPTIONS);
                            }
                        }
                        elseif (
                           $input['Type'] != self::INPUTTYPE_CLASSIC
                        && $input['Type'] != self::INPUTTYPE_SELECT
                        && $input['Type'] != self::INPUTTYPE_EMAIL
                        && $input['Type'] != self::INPUTTYPE_TEL
                        && $input['Type'] != self::INPUTTYPE_CLASSE
                        && $input['Type'] != self::INPUTTYPE_DATE
                        )
                        {
                            $erreurs[] = err::e(err::ARTFORM_BADTYPE);
                        }
                    }
                    else
                    {
                        $erreurs[] = err::e(err::ARTFORM_MISSPARAM);
                    }
                }
                if (sizeof($erreurs) > 0)
                {
                    return $erreurs;
                }
                else
                {
                    return self::createFormInputs($jsonarray);
                }
            }
            else
            {
                return err::e(err::ARTFORM_NOINPUTS);
            }
        }
    }

    private static function createFormInputs($jsonarray)
    {
        $array = [];
        foreach ($jsonarray as $key => $input)
        {
            if ($input['Type'] == self::INPUTTYPE_SELECT)
            {
                $array[] = [
                    'Name' => $key,
                    'Titre' => $input['Titre'],
                    'Type' => $input['Type'],
                    'Requis' => (bool) $input['Requis'],
                    'Regex' => $input['Regex'],
                    'SelectData' => $input['SelectData']
                ];
            }
            else
            {
                $array[] = [
                    'Name' => $key,
                    'Titre' => $input['Titre'],
                    'Type' => $input['Type'],
                    'Requis' => (bool) $input['Requis'],
                    'Regex' => $input['Regex']
                ];
            }
        }
        return $array;
    }


    // Recupérer le code html du formulaire voulu de <form> à </form>
    // renvoi "" si FormInputs == NULL ou FormInputs === false
    public function getForm()
    {
        if ($this->_FormInputs == NULL || $this->_FormInputs === false)
        {
            return '';
        }
        else
        {
            $form = self::createFormHTML($this->_FormInputs);
            $form .=    '<p style="text-align:center">
                            <button type="submit" name="ArticleForm" value="'.$this->_ID.'" class="button primary">Envoyer</button>
                        </p>
                        </form>';
            return $form;
        }
    }

    public static function getFormStatic(array $formInputs)
    {
        $form = self::createFormHTML($formInputs);
        $form .=    '<p style="text-align:center">
                        <button type="submit" name="TEST" value="TEST" class="button primary" disabled>Envoyer</button>
                    </p>
                    </form>';
        return $form;
        
    }

    private static function createFormHTML($formInputs)
    {
        $form = '<form action="" method="post">';
        foreach ($formInputs as $InputData)
        {
            if ($InputData['Type'] == self::INPUTTYPE_CLASSIC)
            {
                $form .= '<input type="text" name="'.$InputData['Name'].'" placeholder="'.$InputData['Titre'].'" required/>';
            }
            elseif ($InputData['Type'] == self::INPUTTYPE_SELECT && sizeof($InputData['SelectData']) > 0)
            {
                $form .= '<select name="'.$InputData['Name'].'" required>';
                $form .= '<option value="'.$InputData['Name'].'" disabled selected>'.$InputData['Titre'].'</option>'; 
                foreach ($InputData['SelectData'] as $value => $titre)
                {
                    $form .= '<option value="'.$value.'">'.$titre.'</option>'; 
                }
                $form .= '</select>';                                                   
            }
            elseif ($InputData['Type'] == self::INPUTTYPE_CLASSE)
            {
                $form .= '<select name="'.$InputData['Name'].'" required>';
                $form .= '<option value="'.$InputData['Name'].'" disabled selected>'.$InputData['Titre'].'</option>'; 
                foreach (settings::p('gestion_adherents')['classes'] as $classe)
                {
                    $form .= '<option value="'.$classe.'">'.$classe.'</option>';
                }
                $form .= '</select>';     
            }
            elseif ($InputData['Type'] == self::INPUTTYPE_EMAIL)
            {
                $form .= '<input type="mail" name="'.$InputData['Name'].'" placeholder="'.$InputData['Titre'].'" required/>';
            }
            elseif ($InputData['Type'] == self::INPUTTYPE_TEL)
            {
                $form .= '<input type="tel" name="'.$InputData['Name'].'" placeholder="'.$InputData['Titre'].'" required/>';
            }
            elseif ($InputData['Type'] == self::INPUTYPE_DATE)
            {
                $form .= '<input type="date" name="'.$InputData['Name'].'" placeholder="'.$InputData['Titre'].'" required/>';
            }
            else
            {
                $form .= '<p>Input type error</p><br>';
            }
        }
        return $form;
    }

    public function verifyFormPost(array $post)
    {
        if ($this->_FormInputs == NULL || $this->_FormInputs === false)
        {
            return false;
        }
        else
        {
            foreach ($this->_FormInputs as $InputData)
            {
                if (!isset($post[$InputData['Name']]))
                {
                    $result = false;
                    break;
                }
                else
                {
                    $result = true;
                }
            }
            return $result;
        }
    }



}




?>

