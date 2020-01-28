<?php
include('../../dependances/class/base.php');
$gestion_events = new gestion_events($bdd, $gestion_adherents);

if (isset($_REQUEST['auth_key']))
{
    if ($gestion_adherents->auth_key_verifKey($_REQUEST['auth_key']) !== false)
    // if (true)
    {
        if (isset($_REQUEST['ID_billet']))
        {
            $ID_billet = $_REQUEST['ID_billet'];
            
            $billet = $gestion_events->getBillet($ID_billet);
            

            if ($billet)
            {
                // récupération de l'id d'inscription
                $req = $bdd->web()->prepare('SELECT ID_inscription FROM events_billets WHERE ID_billet = :ID_billet');
                $req->execute(['ID_billet' => $ID_billet]);
                $req = $req->fetch(PDO::FETCH_ASSOC);
                
                $inscription = $gestion_events->getInscription($req['ID_inscription']);
                unset($req);
                if ($inscription)
                {

                    $json_array['idi'] = $ID_billet;

                    // payer_infos
                    $json_array['payer_infos']['nom'] = $inscription->Nom;
                    $json_array['payer_infos']['prenom'] = $inscription->Prenom;
                    $json_array['payer_infos']['classe'] = $inscription->Classe;
                    $json_array['payer_infos']['email'] = $inscription->Email;
                    if (is_null($inscription->ID_adherent))
                    {
                        $json_array['payer_infos']['auth'] = false;
                        $json_array['payer_infos']['ida'] = "-";
                    }
                    else
                    {
                        $json_array['payer_infos']['auth'] = true;
                        $json_array['payer_infos']['ida'] = $inscription->ID_adherent;
                    }
                    $json_array['payer_infos']['custom_infos'] = $inscription->event_CustomInfos;

                    // order_infos
                    $json_array['order_infos']['tarif'] = $billet->tarif_Nom;
                    $json_array['order_infos']['prix'] = $billet->tarif_Prix;
                    $json_array['order_infos']['custom_infos'] = $billet->tarif_CustomInfos;

                    // paiement_infos
                    // Récuperer le paiement lié au billet
                    foreach($inscription->paiements_IDs as $ID_paiement)
                    {
                        $paiement = $gestion_events->getPaiement($inscription->event_slug, $ID_paiement);
                        $paiement_billets_IDs = json_decode($paiement['ID_billet'], true);
                        if  (   (is_string($paiement_billets_IDs) && $paiement_billets_IDs == $ID_billet)
                                || is_array($paiement_billets_IDs) && in_array($ID_billet, $paiement_billets_IDs)
                            )
                        {
                            $linked_paiement = $paiement;
                            break;
                        }
                    }
                    if (isset($linked_paiement))
                    {
                        $json_array['paiement_infos']['type'] = $paiement['Type'];
                        $json_array['paiement_infos']['paiement_id'] = $paiement['ID_paiement'];
                        $json_array['paiement_infos']['status'] = $paiement['Status'];
                        $json_array['paiement_infos']['prix'] = $paiement['Prix'];
                    }
                    else
                    {
                        $json_array['paiement_infos']['type'] = "NOT_FOUND";
                        $json_array['paiement_infos']['paiement_id'] = "NOT_FOUND";
                        $json_array['paiement_infos']['status'] = "NOT_FOUND";
                        $json_array['paiement_infos']['prix'] = "NOT_FOUND";
                    }

                    /**
                     * STATUS DU BILLET
                     */
                    if (isset($_REQUEST['valid_on_check']) && $_REQUEST['valid_on_check'] == true)
                    {
                        $auto_check = $gestion_events->autoCheck($ID_billet);
                        if ($auto_check)
                        {
                            $json_array['validation_status'] = "JUST";
                        }
                        else
                        {
                            $json_array['validation_status'] = "NOT";
                        }
                    }
                    else
                    {
                        $status = $gestion_events->getBilletStatus($ID_billet);
                        if ($status == "CHECKED")
                        {
                            $json_array['validation_status'] = "ALREADY";
                        }
                        else
                        {
                            $json_array['validation_status'] = "NOT";
                        }
                    }
                }
                else
                {
                    $json_array['error'] = "CANT_FIND_INSCRIPTION";
                }
            }
            else
            {
                $json_array['error'] = "INVALID_IDI";
            }
        }
        else if (isset($_REQUEST['valid']))
        {
            $this->setBilletStatus($_REQUEST['valid'], "CHECKED");
        }
        else if (isset($_REQUEST['unvalid']))
        {
            $this->setBilletStatus($_REQUEST['unvalid'], "NOT_CHECKED");
        }
        else
        {
            $json_array['error'] = "NO_REQUEST";
        }
    }
    else
    {
        $json_array['error'] = "INVALID_AUTH_KEY";
    }
}
else
{
    $json_array['error'] = "NO_REQUEST";
}

header('Content-Type: application/json');
echo json_encode($json_array, JSON_PRETTY_PRINT);



?>