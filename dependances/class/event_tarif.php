<?php

class event_tarif
{


    public  $Slug,
            $Prix_nonAdh,
            $Prix_Adh,

            $Helloasso_nonAdh,
            $Helloasso_Adh,

            $Helloasso_nonAdh_url,
            $Helloasso_Adh_url,

            $Helloasso_nonAdh_Name,
            $Helloasso_Adh_Name,

            $Nom,
            $Description,

            $CustomInputs;

    public function __construct($Nom, $Prix_nonAdh, $Prix_Adh, $Description, $Helloasso_nonAdh, $Helloasso_Adh, $Helloasso_nonAdh_url, $Helloasso_Adh_url, $CampaingsNames, $CustomInputs)
    {
        $this->Nom = $Nom;
        $this->Slug = slugify($Nom);
        $this->Prix_nonAdh = (float) $Prix_nonAdh;
        $this->Prix_Adh = (float) $Prix_Adh;
        $this->Description = $Description;
        $this->Helloasso_nonAdh = $Helloasso_nonAdh;
        $this->Helloasso_Adh = $Helloasso_Adh;
        $this->Helloasso_nonAdh_url = $Helloasso_nonAdh_url;
        $this->Helloasso_Adh_url = $Helloasso_Adh_url;
        if (!is_null($CampaingsNames))
        {
            $this->Helloasso_nonAdh_Name = $CampaingsNames[$this->Helloasso_nonAdh];
            $this->Helloasso_Adh_Name = $CampaingsNames[$this->Helloasso_Adh];
        }
        else
        {
            $this->Helloasso_nonAdh_Name = NULL;
            $this->Helloasso_Adh_Name = NULL;
        }
        $this->CustomInputs = $CustomInputs;
    }

    public function getPrixEuro($tarif = "", $multiplicateur = 1)
    {
        if ($tarif == "Adh")
        {
            return $multiplicateur*$this->Prix_Adh."€";
        }
        else
        {
            return $multiplicateur*$this->Prix_nonAdh."€";
        }
    }

    public function AskCustomInputs()
    {
        $CIs = $this->CustomInputs;
        return $CIs != NULL && is_array($CIs) && sizeof($CIs) > 0;
    }

    public function getInputs()
    {
        return $this->CustomInputs;
    }

    public function exportToArray()
    {
        $export_CustomInputs = [];
        foreach($this->CustomInputs as $CI)
        {
            $export_CustomInputs[] = $CI->exportToArray();
        }
        
        $export_array =     [ 'Slug' => $this->Slug,
                            'Prix_nonAdh' => $this->Prix_nonAdh,
                            'Prix_Adh' => $this->Prix_Adh,
                            'Helloasso_nonAdh' => $this->Helloasso_nonAdh,
                            'Helloasso_Adh' => $this->Helloasso_Adh,
                            'Helloasso_nonAdh_url' => $this->Helloasso_nonAdh_url,
                            'Helloasso_Adh_url' => $this->Helloasso_Adh_url,
                            'Nom' => $this->Nom,
                            'Description' => $this->Description,
                            'CustomInputs' => $export_CustomInputs ];

        return $export_array;

    }

    public static function importFromArray($array)
    {
        if  (isset($array['Slug'])
            && isset($array['Prix_nonAdh'])
            && isset($array['Prix_Adh'])
            && isset($array['Helloasso_nonAdh'])
            && isset($array['Helloasso_nonAdh_url'])
            && isset($array['Helloasso_Adh'])
            && isset($array['Helloasso_Adh_url'])
            && isset($array['Nom'])
            && isset($array['Description'])
            && (isset($array['CustomInputs']) || is_null($array['CustomInputs'])))
        {
            $CustomInputs = [];
            foreach ($array['CustomInputs'] as $CI)
            {
                $CustomInputs[] = event_custominput::importFromArray($CI);
            }
            return new self($array['Nom'], $array['Prix_nonAdh'], $array['Prix_Adh'], $array['Description'], $array['Helloasso_nonAdh'], $array['Helloasso_Adh'], $array['Helloasso_nonAdh_url'], $array['Helloasso_Adh_url'], NULL, $CustomInputs);
        }
        else
        {
            return NULL;
        }
    }





}


?>