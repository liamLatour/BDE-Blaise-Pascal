<?php

class helloasso
{
    private $_bdd;
    
    
    public function __construct($bdd, $gestion_events)
    {
        $this->_bdd = $bdd;
        $this->_gestion_events = $gestion_events;
    }

    public function newPaiement($request)
    {
        if (isset($request['id']) && isset($request['date']) && isset($request['action_id']))
        {
            // bdd paiement
            $req = $this->_bdd->web()->prepare('INSERT INTO events_helloasso_paiements 
            (ha_paiement_ID, date, amount, url, payer_first_name, payer_last_name, url_receipt, url_tax_receipt, mean, ha_action_ID) 
            VALUES(:ha_paiement_ID, :date, :amount, :url, :payer_first_name, :payer_last_name, :url_receipt, :url_tax_receipt, :mean, :ha_action_ID)');           
            $res = $req->execute([
                'ha_paiement_ID' => $request['id'],
                'date' => $request['date'],
                'amount' => (float) $request['amount'],
                'url' => $request['url'],
                'payer_first_name' => $request['payer_first_name'],
                'payer_last_name' => $request['payer_last_name'],
                'url_receipt' => $request['url_receipt'],
                'url_tax_receipt' => $request['url_tax_receipt'],
                'mean' => $request['mean'],
                'ha_action_ID' => $request['action_id']
            ]);

            // bdd action
            for ($i=0; $i < 10; $i++)
            { 
                $action = self::getAction($request['action_id']);
                if (isset($action['id']))
                {
                    break;
                }
                else
                {
                    sleep(2);
                }
            }

            $req = $this->_bdd->web()->prepare('INSERT INTO events_helloasso_actions 
            (ha_action_ID, ha_campaign_ID, ha_paiement_ID, date, amount, first_name, last_name, email, custom_infos, status) 
            VALUES(:ha_action_ID, :ha_campaign_ID, :ha_paiement_ID, :date, :amount, :first_name, :last_name, :email, :custom_infos, :status)');           
            $res = $req->execute([
                'ha_action_ID' => $action['id'],
                'ha_campaign_ID' => $action['id_campaign'],
                'ha_paiement_ID' => $action['id_payment'],
                'date' => $action['date'],
                'amount' => (float) $action['amount'],
                'first_name' => $action['first_name'],
                'last_name' => $action['last_name'],
                'email' => $action['email'],
                'custom_infos' => json_encode($action['custom_infos'], JSON_PRETTY_PRINT),
                'status' => $action['status']
            ]);

            // gestion du paiement
            $this->updateEventPaiement($action);
        }
    }

    public function updateEventPaiement($action)
    {
       
        // email
        $email = $action['email'];

        // array_label
        $label_paiment_array = [
            strtoupper(stripAccents("IDENTIFIANT DE PAIEMENT")),
            strtoupper(stripAccents("numéro de paiement")),
            strtoupper(stripAccents("référence de paiement"))
        ];
        $label_inscription_array = [
            strtoupper(stripAccents("numéro d'inscription")),
            strtoupper(stripAccents("référence d'inscription")),
            strtoupper(stripAccents("IDENTIFIANT D'inscription"))
        ];
        
        // récuperer l'ID_paiement et de billet
        foreach ($action['custom_infos'] as $info)
        {
            // paiement
            if (in_array(strtoupper(stripAccents($info['label'])), $label_paiment_array))
            {
                $ID_paiement = $info['value'];
            }
            // inscription
            else if (in_array(strtoupper(stripAccents($info['label'])), $label_inscription_array))
            {
                $ID_inscription = $info['value'];
            }
        }
        if (isset($ID_paiement) && isset($ID_inscription)) // vérifier qu'on a les 2 ids et l'email
        {
            /**
             * 1- passer par l'inscription
             */
            $inscription = $this->_gestion_events->getInscription($ID_inscription);
            if ($inscription)
            {
                $paiement = $this->_gestion_events->getPaiement($inscription->event_slug, $ID_paiement);
                if ($paiement && $paiement['Status'] == "WAITING")
                {
                    // if ($inscription->Email == $email) // Tout est OK!
                    // {
                        $this->_gestion_events->updatePaiement($inscription->event_slug, $ID_paiement, $action['id_payment'], "CONFIRMED_HELLOASSO", true);                     
                        $req = $this->_bdd->web()->prepare('UPDATE events_helloasso_paiements SET Linked_ID_paiement = :Linked_ID_paiement, Linked_ID_inscription = :Linked_ID_inscription WHERE ha_paiement_ID = :ha_paiement_ID');
                        $req->execute([
                            'ha_paiement_ID' => $action['id_payment'],
                            'Linked_ID_paiement' => $ID_paiement,
                            'Linked_ID_inscription' => $ID_inscription
                        ]);
                    // }
                    // else {} // Le mail n'est pas bon
                }
                else {} // l'id_paiement n'est pas valide ou ne correspond pas à cet event_slug
            }
            else // l'id_inscription n'est pas valide
            {
                /**
                 * 2- passer par la campagne
                 */
                // il peut y avoir plusieurs events lié à une campagne, on recupère tout les slugs qui correspondent à cette campagne
                $event_slug_array = $this->getEvents_slugsForCampaignID($action['id_campaign']);
                $event_slug_array = array_unique($event_slug_array);
                $paiements_array = [];
                foreach($event_slug_array as $event_slug) // pour tout ces slugs, si un paiement est trouvé et qu'il est "WAITING" on le stoque
                {
                    $paiement = $this->_gestion_events->getPaiement($event_slug, $ID_paiement);
                    if ($paiement && $paiement['Status'] == "WAITING")
                    {
                        $paiements_array[] = $paiement;
                    }
                }
                // Vérifier le nombre de paiements id trouvés
                if (sizeof($paiements_array) == 0) { } // Aucun paiement trouvé avec cet ID
                else if (sizeof($paiements_array) > 1) { } // Plusieurs paiements trouvés
                else // Un seul paiement trouvé
                {
                    $inscription = $this->_gestion_events->getInscription($paiements_array[0]['ID_inscription']);
                    // if ($inscription->Email == $email) // Tout est OK!
                    // {
                        $this->_gestion_events->updatePaiement($inscription->event_slug, $ID_paiement, $action['id_payment'], "CONFIRMED_HELLOASSO", true);
                        $req = $this->_bdd->web()->prepare('UPDATE events_helloasso_paiements SET Linked_ID_paiement = :Linked_ID_paiement, Linked_ID_inscription = :Linked_ID_inscription WHERE ha_paiement_ID = :ha_paiement_ID');
                        $req->execute([
                            'ha_paiement_ID' => $action['id_payment'],
                            'Linked_ID_paiement' => $ID_paiement,
                            'Linked_ID_inscription' => $paiements_array[0]['ID_inscription']
                        ]);
                    // }
                    // else {} // Le mail n'est pas bon
                }
            }
            

        }
    }

    public function checkPaiement()
    {

    }

    public function getEvents_slugsForCampaignID($ha_campaign_ID)
    {
        $req = $this->_bdd->web()->prepare('SELECT event_slug FROM events_helloasso_campaigns WHERE ha_campaign_ID = :ha_campaign_ID');
        $req->execute(['ha_campaign_ID' => $ha_campaign_ID]);
        $res = $req->fetchAll(PDO::FETCH_ASSOC);
        $event_slug_array = [];
        foreach ($res as $event_slug)
        {
            $event_slug_array[] = $event_slug['event_slug'];
        }
        return $event_slug_array;
    }

    
    
    private static function Request($arg)
    {
        $APIKEY = 'bde-de-blaise-pascal';
        $APIPASSWORD = 'h8eMftCq7oSbfDiYJo69q';

        $start_url = "https://api.helloasso.com/v3/";

        $curl = curl_init();
        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $APIKEY . ":" . $APIPASSWORD);
        curl_setopt($curl, CURLOPT_URL, $start_url.$arg);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $json = curl_exec($curl);
        curl_close($curl); 

        return $json;

    }




    public static function getAllCampaigns()
    {
        $json = self::Request("campaigns.json?results_per_page=1000");
        $array = json_decode($json, true);
        if (isset($array['resources']) && sizeof($array['resources']) > 0)
        {
            return $array['resources'];
        }
        else
        {
            return false;
        }
    }

    public static function getAllCampaingsNames()
    {
        $AllCampaings = self::getAllCampaigns();
        $Names = [];
        foreach ($AllCampaings as $campaign)
        {
            $Names[$campaign['id']] = $campaign['name'];
        }
        if (sizeof($Names) > 0)
        {
            return $Names;
        }
        else
        {
            return false;
        }
    }

    public static function getAction($action_ID)
    {
        $json = self::Request("actions/".$action_ID.".json");
        $array = json_decode($json, true);
        if (isset($array['id']))
        {
            return $array;
        }
        else
        {
            return false;
        }
    }



}



?>