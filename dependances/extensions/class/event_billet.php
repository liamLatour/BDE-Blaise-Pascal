<?php

class event_billet
{

    public  $ID,

            $Nom,
            $Prenom,
            $Classe,
            $Email,
            $ID_adherent,

            $event_CustomInfos,


            $tarif_Nom,
            $tarif_Slug,
            $tarif_CustomInfos,

            $prix_Type,
            $tarif_Prix,

            $TimeStamp;

    public function __construct(    $ID,
                                    $Nom,
                                    $Prenom,
                                    $Classe,
                                    $Email,
                                    $ID_adherent,
                                    $event_CustomInfos,
                                    $tarif_Nom,
                                    $tarif_Slug,
                                    $tarif_CustomInfos,
                                    $prix_Type,
                                    $tarif_Prix,
                                    $TimeStamp     )
    {
        $this->ID = $ID;
        $this->Nom = $Nom;
        $this->Prenom = $Prenom;
        $this->Classe = $Classe;
        $this->Email = $Email;
        $this->ID_adherent = $ID_adherent;
        $this->event_CustomInfos = $event_CustomInfos;
        $this->tarif_Nom = $tarif_Nom;
        $this->tarif_Slug = $tarif_Nom;
        $this->tarif_CustomInfos = $tarif_CustomInfos;
        $this->prix_Type = $prix_Type;
        $this->tarif_Prix = $tarif_Prix;
        $this->TimeStamp = $TimeStamp;
    }

    public function setTimeStamp($TimeStamp)
    {
        $this->TimeStamp = $TimeStamp;
    }

    public function get_CustomForm_event_String($event)
    {
        $string = "";
        if (sizeof($this->event_CustomInfos) > 0)
        {
            $Inputs = $event->getInputs();
            if (sizeof($Inputs) > 0) // Vérifie contient des cis
            {
                foreach($Inputs as $Ipt)
                {
                    if (isset($this->event_CustomInfos[$Ipt->Name]))
                    {
                        $string .= " - ".$Ipt->Label." : ".$this->event_CustomInfos[$Ipt->Name]."\n";
                    }
                }    
            }
        }
        return $string;
    }

    public function get_CustomForm_Tarif_String($event)
    {
        $string = "";
        if (sizeof($this->tarif_CustomInfos) > 0)
        {
            $Tarif = $event->getTarifBySlug($this->tarif_Slug);
            if ($Tarif) // Vérifie que le tarif existe bien
            {
                $Inputs = $Tarif->getInputs();
                if (sizeof($Inputs) > 0) // Vérifie contient des cis
                {
                    foreach($Inputs as $Ipt)
                    {
                        if (isset($this->tarif_CustomInfos[$Ipt->Name]))
                        {
                            $string .= " - ".$Ipt->Label." : ".$this->tarif_CustomInfos[$Ipt->Name]."\n";
                        }
                    }    
                }
            }
        }
        return $string;
    }

    public function exportToString()
    {
        return json_encode($this->exportToArray(), JSON_PRETTY_PRINT);
    }

    public function exportToArray()
    {
        $export_array =   [ 'ID' => $this->ID,
                            'Nom' => $this->Nom,
                            'Prenom' => $this->Prenom,
                            'Classe' => $this->Classe,
                            'Email' => $this->Email,
                            'ID_adherent' => $this->ID_adherent,
                            'event_CustomInfos' => $this->event_CustomInfos,
                            'tarif_Nom' => $this->tarif_Nom,
                            'tarif_Slug' => $this->tarif_Slug,
                            'tarif_CustomInfos' => $this->tarif_CustomInfos,
                            'prix_Type' => $this->prix_Type,
                            'tarif_Prix' => $this->tarif_Prix,
                            'TimeStamp' => $this->TimeStamp ];

        return $export_array;

    }

    public static function importFromString($string)
    {
        $array = json_decode($string, true);
        return self::importFromArray($array);
    }

    public static function importFromArray($array)
    {
        if    (isset($array['ID'])
            && isset($array['Nom'])
            && isset($array['Prenom'])
            && isset($array['Classe'])
            && isset($array['Email'])
            && (isset($array['ID_adherent']) || is_null($array['ID_adherent']))
            && (isset($array['event_CustomInfos']) || is_null($array['event_CustomInfos']))
            && isset($array['tarif_Nom'])
            && isset($array['tarif_Slug'])
            && (isset($array['tarif_CustomInfos']) || is_null($array['tarif_CustomInfos']))
            && isset($array['prix_Type'])
            && isset($array['tarif_Prix'])
            && isset($array['TimeStamp'])
            )
        {
            return new self(    $array['ID'],
                                $array['Nom'],
                                $array['Prenom'],
                                $array['Classe'],
                                $array['Email'],
                                $array['ID_adherent'],
                                $array['event_CustomInfos'],
                                $array['tarif_Nom'],
                                $array['tarif_Slug'],
                                $array['tarif_CustomInfos'],
                                $array['prix_Type'],
                                $array['tarif_Prix'],
                                $array['TimeStamp']   );
        }
        else
        {
            return NULL;
        }
    }


}










?>