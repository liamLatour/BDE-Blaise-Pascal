<?php

class event
{


    private $_ID,
            $_Slug,

            $_Titre,
            $_Soustitre,
            $_Miniature_slug,
            $_Banner_slug,

            $_Description,

            $_Tarifs,
            $_TarifsSlugs,

            $_Inputs,
            $_CustomInputs,

            $_MultiAuth,
            $_MailTemplate,
            $_ShowConditions,

            $_settings;


    /**
     * 
     * CREATION
     * 
    */  
    
    /**
     * MAIN_INFOS
     */
    public function __construct($Titre, $Miniature_slug = '', $Banner_slug = '', $Soustitre = '', $Description = '')
    {
        $this->_Titre = $Titre;
        $this->_Slug = slugify($Titre);
        $this->_Miniature_slug = $Miniature_slug;
        $this->_Banner_slug = $Banner_slug;
        $this->_Soustitre = $Soustitre;
        $this->_Description = $Description;

        $this->_Tarifs = [];
        $this->_TarifsSlugs = [];
        $this->_CustomInputs = [];
        $this->updateInputs();

        $this->_settings = settings::p("event");
    }

    public function setOtherInfos($MultiAuth, $MailTemplate, $event_showconditions)
    {
        $this->_MultiAuth = $MultiAuth;
        $this->_MailTemplate = $MailTemplate;
        $this->_ShowConditions = $event_showconditions;
    }

    public function getImage($slug)
    {;
        if ($slug != '')
        {
            return "https://docs.bde-bp.fr/images/imgprnt.php?i=".$slug;
        }
        else
        {
            return "https://docs.bde-bp.fr/images/imgprnt.php?i=no-image";
        }
    }

    public function getSlug()
    {
        return $this->_Slug;
    }

    public function getMainInfos()
    {
        $array = [  'Titre' => $this->_Titre,
                    'Slug' => $this->_Slug,
                    'Miniature' => $this->getImage($this->_Miniature_slug),
                    'Miniature_slug' => $this->_Miniature_slug,
                    'Banner' => $this->getImage($this->_Banner_slug),
                    'Banner_slug' => $this->_Banner_slug,
                    'Soustitre' => $this->_Soustitre,
                    'Description' => $this->_Description,
                    'MultiAuth' => $this->_MultiAuth,
                    'MailTemplate' => $this->_MailTemplate ];

        if (is_a($this->_ShowConditions, "event_showconditions"))
        {
            $array = array_merge($array, ['ShowConditions_date_start' => $this->_ShowConditions->getVariables()['date_start'],
                                'ShowConditions_date_stop' => $this->_ShowConditions->getVariables()['date_stop'],
                                'ShowConditions_show' => $this->_ShowConditions->getVariables()['show']]);
        }
         
        return $array;
    }

    public function actif()
    {
        return $this->_ShowConditions->show();
    }

    public function MultiAuth()
    {
        return (bool) $this->_MultiAuth;
    }

    /**
     * INPUTS
     */

    public function setCustomInputs($CustomInputs)
    {
        $this->_CustomInputs = $CustomInputs;
        $this->updateInputs();
    }

    private function updateInputs()
    {
        $Base = [
            new event_custominput(  "nom",
                                    "Nom",
                                    "Nom",
                                    event_custominput::TYPE_STRING,
                                    true,
                                    $this->_settings["PNom_RegEx"],
                                    "Le nom ne doit contenir que des lettres et accents, et faire entre 3 et 30 caractères."
                                ),
            new event_custominput(  "prenom",
                                    "Prénom",
                                    "Prénom",
                                    event_custominput::TYPE_STRING,
                                    true,
                                    $this->_settings["PNom_RegEx"],
                                    "Le prénom ne doit contenir que des lettres et accents, et faire entre 3 et 30 caractères."),
            new event_custominput(  "email",
                                    "Adresse mail",
                                    "exemple@exemple.fr",
                                    event_custominput::TYPE_EMAIL, true,
                                    $this->_settings["Email_RegEx"],
                                    "L'adresse mail ne remplit pas les conditions usuelles."),
            new event_custominput("classe", "Classe", "Selectionnez votre classe", event_custominput::TYPE_SELECT, true, NULL, NULL, event_custominput::buildOptionsArray("MPSI1;MPSI2;PCSI1;PCSI2;BCPST1;ECS1;ECE1;HK1;HK2;MP;MPX;PSI;PC;PCX;BCPST2;ECE2;ECS2;KH"))
        ];
        $this->_Inputs = array_merge($Base, $this->_CustomInputs);
    }

    public function getInputs()
    {
        return $this->_Inputs;
    }

    public static function buildCustomInputsArray(string $custominputs_string)
    {
        if (mb_substr($custominputs_string, -1) == ";")
        {
            $custominputs_string = mb_substr($custominputs_string, 0, -1);
        }
        
        $custominputs_array = explode(";", $custominputs_string);

        $CustomInputs = [];
        foreach($custominputs_array as $ci)
        {
            $input = event_custominput::import($ci);
            if ($input != NULL)
            {
                $CustomInputs[] = event_custominput::import($ci);
            }
        }

        return $CustomInputs;
    }

    public function exportCustomInputs()
    {
        $CustomInputs = $this->_CustomInputs;
        $custominputs_string = "";
        foreach ($CustomInputs as $Input)
        {
            $custominputs_string .= $Input->export().";";
        }
        if (mb_substr($custominputs_string, -1) == ";")
        {
            $custominputs_string = mb_substr($custominputs_string, 0, -1);
        }
        return $custominputs_string;
    }

    /**
     * TARIFS
     */
    public function addTarif($Tarif)
    {
        if (is_a($Tarif, "event_tarif"))
        {
            //if (!in_array($Tarif->Slug, $this->_TarifsSlugs))
            if (!$this->checkTarif($Tarif->Slug))
            {
                $this->_TarifsSlugs[] = $Tarif->Slug;
                $this->_Tarifs[] = $Tarif;
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

    public function getTarifs()
    {
        return $this->_Tarifs;
    }

    public function getTarifBySlug($slug)
    {
        $loop = false;
        foreach($this->_Tarifs as $Tarif)
        {
            if ($Tarif->Slug == $slug)
            {
                $loop = true;
                $return_tarif = $Tarif;
                break;
            }
        }
        if ($loop)
        {
            return $return_tarif;
        }
        else
        {
            return false;
        }
    }

    public function getTarifsSlugs()
    {
        return $this->_TarifsSlugs;
    }

    public function tarifsOk()
    {
        return is_array($this->_Tarifs) && sizeof($this->_Tarifs) > 0;
    }

    public function checkTarif($slug)
    {
        return in_array($slug, $this->_TarifsSlugs);
    }

    public function exportToString()
    {
        $export_Tarifs = [];
        foreach($this->_Tarifs as $Tarif)
        {
            $export_Tarifs[] = $Tarif->exportToArray();
        }

        $export_CustomInputs = [];
        foreach($this->_CustomInputs as $CI)
        {
            $export_CustomInputs[] = $CI->exportToArray();
        }


        
        
        
        $export_array = [  'Slug' => $this->_Slug,
                            'Titre' => $this->_Titre,
                            'Soustitre' => $this->_Soustitre,
                            'Miniature_slug' =>$this->_Miniature_slug,
                            'Banner_slug' => $this->_Banner_slug,
                            'Description' => $this->_Description,
                            'Tarifs' => $export_Tarifs,
                            'CustomInputs' => $export_CustomInputs,
                            'MultiAuth' => $this->_MultiAuth,
                            'MailTemplate' => $this->_MailTemplate,
                            'ShowConditions' => $this->_ShowConditions->exportToArray() ];

        return json_encode($export_array, JSON_PRETTY_PRINT);

    }

    public static function importFromString($string)
    {
        $import_array = json_decode($string, true);
        if ($import_array && isset($import_array['Slug']))
        {
            $event = new self($import_array['Titre'],
                            $import_array['Miniature_slug'],
                            $import_array['Banner_slug'],
                            $import_array['Soustitre'],
                            $import_array['Description']);

            $ShowConditions = event_showconditions::importFromArray($import_array['ShowConditions']);           
            $event->setOtherInfos($import_array['MultiAuth'], $import_array['MailtTemplate'], $ShowConditions);


            $CustomInputs = [];
            foreach ($import_array['CustomInputs'] as $CI)
            {
                $CustomInputs[] = event_custominput::importFromArray($CI);
            }
            $event->setCustomInputs($CustomInputs);


            foreach ($import_array['Tarifs'] as $Tarif)
            {
                $Tarif = event_tarif::importFromArray($Tarif);
                $event->addTarif($Tarif);
            }

            return $event;

        }
    }






}




?>