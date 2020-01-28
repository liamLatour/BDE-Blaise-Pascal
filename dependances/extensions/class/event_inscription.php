<?php

class event_inscription
{
    

    public  $ID,
    
            $event_slug,

            $Nom,
            $Prenom,
            $Classe,
            $Email,
            $ID_adherent,

            $event_CustomInfos,

            $billets_IDs,
            $billets,
            
            $paiements_IDs,
            $total_prix,

            $TimeStamp;


    public function __construct(    $ID,
                                    $event_slug,
                                    $Nom,
                                    $Prenom,
                                    $Classe,
                                    $Email,
                                    $ID_adherent,
                                    $event_CustomInfos,
                                    $billets_IDs,
                                    $billets,
                                    $paiements_IDs,
                                    $total_prix,
                                    $TimeStamp     )
    {
        $this->ID = $ID;
        $this->event_slug = $event_slug;
        $this->Nom = $Nom;
        $this->Prenom = $Prenom;
        $this->Classe = $Classe;
        $this->Email = $Email;
        $this->ID_adherent = $ID_adherent;
        $this->event_CustomInfos = $event_CustomInfos;
        $this->billets_IDs = $billets_IDs;
        $this->billets = $billets;
        $this->paiements_IDs = $paiements_IDs;
        $this->total_prix = $total_prix;
        $this->TimeStamp = $TimeStamp;
    }

    public function exportToArray()
    {
        $billets_export_array = [];
        foreach ($this->billets as $blt)
        {
            $billets_export_array[] = $blt->exportToArray();
        }

        $export_array =   [ 'ID' => $this->ID,
                            'event_slug' => $this->event_slug,
                            'Nom' => $this->Nom,
                            'Prenom' => $this->Prenom,
                            'Classe' => $this->Classe,
                            'Email' => $this->Email,
                            'ID_adherent' => $this->ID_adherent,
                            'event_CustomInfos' => $this->event_CustomInfos,
                            'billets_IDs' => $this->billets_IDs,
                            'billets' => $billets_export_array,
                            'paiements_IDs' => $this->paiements_IDs,
                            'total_prix' => $this->total_prix,
                            'TimeStamp' => $this->TimeStamp ];

        return $export_array;

    }

    public static function importFromArray($array)
    {
        if    (isset($array['ID'])
            && isset($array['event_slug'])
            && isset($array['Nom'])
            && isset($array['Prenom'])
            && isset($array['Classe'])
            && isset($array['Email'])
            && (isset($array['ID_adherent']) || is_null($array['ID_adherent']))
            && (isset($array['event_CustomInfos']) || is_null($array['event_CustomInfos']))
            && isset($array['billets_IDs'])
            && isset($array['billets'])
            && isset($array['paiements_IDs'])
            && isset($array['total_prix'])
            && isset($array['TimeStamp'])
            )
        {
            $billets_import_array = [];
            foreach($array['billets'] as $blt)
            {
                $billets_import_array[] = event_billet::importFromArray($blt);
            }
            
            return new self(    $array['ID'],
                                $array['event_slug'],
                                $array['Nom'],
                                $array['Prenom'],
                                $array['Classe'],
                                $array['Email'],
                                $array['ID_adherent'],
                                $array['event_CustomInfos'],
                                $array['billets_IDs'],
                                $billets_import_array,
                                $array['paiements_IDs'],
                                $array['total_prix'],
                                $array['TimeStamp']   );
        }
        else
        {
            return NULL;
        }
    }



}


?>