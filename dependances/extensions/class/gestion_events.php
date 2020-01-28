<?php

class gestion_events
{
    private $_bdd, $_gestion_adherents;

    const   SESSION_VALID_HOURS = 24;


    public function __construct(bdd $bdd, gestion_adherents $gestion_adherents)
    {
        $this->_bdd = $bdd;
        $this->_gestion_adherents = $gestion_adherents;
    }

    /**
     * GESTION
     */
    public function addEvent($event)
    {
        $slug = $event->getSlug();
        if ($this->getEvent($slug) == false)
        {
            $req = $this->_bdd->web()->prepare('INSERT INTO events (slug, event) VALUES(:slug, :event)');
            $res = $req->execute([
                'slug' => $slug,
                'event' => $event->exportToString()
            ]);

            foreach($event->getTarifs() as $Tarif)
            {
                $req1 = $this->_bdd->web()->prepare('INSERT INTO events_helloasso_campaigns (ha_campaign_ID, event_slug, tarif_slug, tarif_type) VALUES(:ha_campaign_ID, :event_slug, :tarif_slug, :tarif_type)');
                $res1 = $req1->execute([
                    'ha_campaign_ID' => $Tarif->Helloasso_nonAdh,
                    'event_slug' => $slug,
                    'tarif_slug' => $Tarif->Slug,
                    'tarif_type' => "NON_ADHERENT"
                ]);
                $req2 = $this->_bdd->web()->prepare('INSERT INTO events_helloasso_campaigns (ha_campaign_ID, event_slug, tarif_slug, tarif_type) VALUES(:ha_campaign_ID, :event_slug, :tarif_slug, :tarif_type)');
                $res2 = $req2->execute([
                    'ha_campaign_ID' => $Tarif->Helloasso_Adh,
                    'event_slug' => $slug,
                    'tarif_slug' => $Tarif->Slug,
                    'tarif_type' => "ADHERENT"
                ]);
            }

            return (bool) $res;
        }
        else
        {
            return false;
        }
    }

    public function getEvent($slug)
    {
        $req = $this->_bdd->web()->prepare('SELECT * FROM events WHERE slug = :slug');
        $req->execute([
            'slug' => $slug
        ]);
        $req = $req->fetch(PDO::FETCH_ASSOC);
        if ($req == false)
        {
            return false;
        }
        else
        {
            return event::importFromString($req['event']); 
        }
    }

    public function checkSanity(event $event_to_check)
    {
        $event_valid = $this->getEvent($event_to_check->getSlug());
        if ($event_valid)
        {
            $event_valid = crc32($event_valid->exportToString());
            $event_to_check = crc32($event_to_check->exportToString());
            return $event_valid == $event_to_check;
        }
        else
        {
            return false;
        }
    }


    // public function getForm($tarif_or_event)
    // {
    //     $form = '<form method="post">'."\n";

    //     $form .= $CI->getInputs($tarif_or_event);

    //     $form .= '<button type="submit" name="'.$tarif_or_event->Slug.'">Continuer</button>'."\n";
    //     $form .= '</form>'."\n";
    // } 

    public function getInputs($tarif_or_event)
    {
        $inputs = "";
        $inputs_array = $tarif_or_event->getInputs();
        if (is_array($inputs_array) && sizeof($inputs_array) > 0)
        {
            foreach ($inputs_array as $CI)
            {
                $inputs .= $CI->getInput();
            }
        }
        return $inputs;
    }

    public function getGlobalForm($event, $link_prefix)
    {
        if (isset($_SESSION['events']['register']['session']))
        {
            $session = $_SESSION['events']['register']['session'];
            if (isset($session['content']['GLOBALFORM']['post']))
            {
                $prev_post = $session['content']['GLOBALFORM']['post'];
            }

            $globalform = '<form action="'.$link_prefix.'" method="post">'."\n";

            $inputs_array = $event->getInputs();
            if (is_array($inputs_array) && sizeof($inputs_array) > 0)
            {
                $connected = $this->_gestion_adherents->isConnected();
                if ($connected)
                {
                    $adherent = $_SESSION['Adherent'];
                }
                $isset_prev_post = isset($prev_post);
                foreach ($inputs_array as $CI)
                {
                    if ($isset_prev_post)
                    {
                        if (isset($prev_post[$CI->Name]))
                        {
                            $CI->setValue($prev_post[$CI->Name]);
                        }
                    }
                    
                    
                    if ($connected)
                    {
                        
                        if ($CI->Name == 'nom')
                        {
                            $CI->setValue($adherent->getNom());
                            $CI->lock();
                        }
                        else if ($CI->Name == 'prenom')
                        {
                            $CI->setValue($adherent->getPrenom());
                            $CI->lock();
                        }
                        else if ($CI->Name == 'classe')
                        {
                            $CI->setValue($adherent->getClasse());
                            $CI->lock();
                        }
                        else if ($CI->Name == 'email')
                        {
                            $CI->setValue($adherent->getEmail());
                            $CI->lock();
                        }
                    }
                       
                    $globalform .= $CI->getInput();
                }
            }


            $globalform .= '<button type="submit" name="GLOBALFORM">Continuer</button>'."\n";
            $globalform .= '</form>'."\n";

            return $globalform;
        }
        else
        {
            return "";
        }
    }

    public function getTarifForm($tarif, $index)
    {
        if (isset($_SESSION['events']['register']['session']))
        {
            $session = $_SESSION['events']['register']['session'];
            $tarif_slug = $tarif->Slug;
            if (isset($session['content']['TARIFS'][$tarif_slug]['CustomForm'][$index]))
            {
                $prev_post = $session['content']['TARIFS'][$tarif_slug]['CustomForm'][$index];
            }

            $inputs_array = $tarif->getInputs();
            if (is_array($inputs_array) && sizeof($inputs_array) > 0)
            {
                $isset_prev_post = isset($prev_post);
                foreach ($inputs_array as $CI)
                {
                    if ($isset_prev_post)
                    {
                        if (isset($prev_post[$CI->Name]))
                        {
                            $CI->setValue($prev_post[$CI->Name]);
                        }
                    }
                    $prefix = $tarif_slug."_".$index;
                    $globalform .= $CI->getInput($prefix);
                }
            }

            return $globalform;
        }
        else
        {
            return "";
        }
    }

    /**
     * 
     * CREATION
     * 
     */
    public static function createEvent_showPrevValues($input)
    {
        if (isset($_SESSION['events']['create']['back_event_obj']))
        {
            $event = $_SESSION['events']['create']['back_event_obj'];
            $MainInfos = $event->getMainInfos();
            switch ($input)
            {
                case "Titre":
                    echo $MainInfos['Titre'];
                    break;
                case "Miniature_slug":
                    echo $MainInfos['Miniature_slug'];
                    break;
                case "Banner_slug":
                    echo $MainInfos['Banner_slug'];
                    break;
                case "Soustitre":
                    echo $MainInfos['Soustitre'];
                    break;
                case "Description":
                    echo $MainInfos['Description'];
                    break;
                case "event_CustomInputs":
                    echo $event->exportCustomInputs();
                    break;
                case "MultiAuth-true":
                    if ($MainInfos['MultiAuth'] == true)
                    {
                        echo 'checked="checked"';
                    }
                    break;
                case "MultiAuth-false":
                    if ($MainInfos['MultiAuth'] == false)
                    {
                        echo 'checked="checked"';
                    }
                    break;
                case "MailTemplate":
                    echo $MainInfos['MailTemplate'];
                    break;
                case "ShowConditions_date_start":
                    echo $MainInfos['ShowConditions_date_start'];
                    break;
                case "ShowConditions_date_stop":
                    echo $MainInfos['ShowConditions_date_stop'];
                    break;
                case "ShowConditions_show-true":
                    if ($MainInfos['ShowConditions_show'] == true)
                    {
                        echo 'checked="checked"';
                    }
                    break;
                case "ShowConditions_show-false":
                    if ($MainInfos['ShowConditions_show'] == false)
                    {
                        echo 'checked="checked"';
                    }
                    break;
            }
        }
    }

    public static function createEvent_getHelloassoCampaingsSelectOptions($CampaignsNames, $selected_option = "")
    {
        // $CampaignsNames = helloasso::getAllCampaingsNames();
        if ($CampaignsNames)
        {
            $CampaignsNames = array_reverse($CampaignsNames);
            $options = "";
            foreach($CampaignsNames as $id => $name)
            {
                if ($selected_option == $id)
                {
                    $selected = 'selected="selected" default';
                }
                else
                {
                    $selected = '';
                }
                $options .= '<option value="'.$id.'" '.$selected.' >'.$name."</option>\n";
            }
            return $options;
        }
        else
        {
            return "";
        }
    }


























    /**
     * 
     * REGISTER
     * 
     */

    public function register_auth($event, $IDA)
    {
        $adherent = $this->_gestion_adherents->getAdherent($IDA);
        if ($adherent && $this->_gestion_adherents->checkPaiementAdherent($adherent)) // Vérifie que l'adherent existe et qu'il a payé
        {
            if (!$event->MultiAuth()) // si l'event n'accepte pas le multiauth
            {
                $req = $this->_bdd->web()->prepare('SELECT * FROM events_inscriptions WHERE ID_adherent = :IDA AND event_slug = :event_slug');
                $req->execute(['IDA' => $IDA, 'event_slug' => $event->getSlug()]);
                $req = $req->fetch(PDO::FETCH_ASSOC);
                if ($req == false) // verifie que la requète est vide, sinon c'est qu'il y a dejà une auth
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
                return true;
            }
        }
        else
        {
            return false;
        }
    }

    public function handleGlobalForm($event, $post)
    {
        $Inputs = $event->getInputs();

        $error_test_loop = false;
        $GlobalForm = [];
        foreach ($Inputs as $Ipt)
        {
            if ($Ipt->Obligatoire && !isset($post[$Ipt->Name])) // Si l'input est obligatoire mais qu'il n'a pas été post
            {
                $error_test_loop = true;
                $input_stop_label = $Ipt->Label;
                $input_stop_errormsg = "Ce champ est obligatoire.";
                break;
            }
            else if (isset($post[$Ipt->Name]) && !$Ipt->checkValue($post[$Ipt->Name])) // Si l'input a été post mais pas correct
            {
                $error_test_loop = true;
                $input_stop_label = $Ipt->Label;
                if ($Ipt->isListType())
                {
                    $input_stop_errormsg = "La valeur renseignée ne fait pas partie des options disponibles.";
                }
                else
                {
                    $input_stop_errormsg = $Ipt->ErrorMsg;
                }          
                break;
            }
            else
            {
                if (isset($post[$Ipt->Name]))
                {
                    $GlobalForm[$Ipt->Name] = $post[$Ipt->Name];
                }
            }
        }

        if ($error_test_loop)
        {
            return ['Return' => false, 'ErrorMsg' => $input_stop_errormsg, 'Label' => $input_stop_label];
        }
        else
        {
            return ['Return' => true, 'GlobalForm' => $GlobalForm];
        }
    }

    public function handleTarifsForm($event, $session_content_Tarifs, $post)
    {
        $selected_tarifs_loop_test = false;
        $CustomForms = [];
        foreach($session_content_Tarifs as $tarif_slug => $tarif_session) // Loop tout les tarifs selectionnés
        {
            $Tarif = $event->getTarifBySlug($tarif_slug);
            if ($Tarif) // Vérifie que le tarif existe bien
            {
                $Inputs = $Tarif->getInputs();
                if (sizeof($Inputs) > 0) // Vérifie qu'il contient bien des cis
                {
                    $quantity_tarif_loop_test = false;
                    for ($i=1; $i <= $tarif_session['Quantity'] ; $i++) // Loop autant de fois le tarif que la quantité selectionnée
                    {
                        $tarif_inputs_loop_test = false;
                        foreach($Inputs as $Ipt) // Loop tout les ipt de ce tarif
                        {
                            $post_index = $tarif_slug."_".$i."_-_".$Ipt->Name;

                            if ($Ipt->Obligatoire && !isset($post[$post_index])) // Si l'input est obligatoire mais qu'il n'a pas été post
                            {
                                $tarif_inputs_loop_test = true;
                                $input_stop_label = $Ipt->Label;
                                $input_stop_errormsg = "Ce champ est obligatoire.";
                                break;
                            }
                            else if (isset($post[$Ipt->Name]) && !$Ipt->checkValue($post[$post_index])) // Si l'input a été post mais pas correct
                            {
                                $tarif_inputs_loop_test = true;
                                $input_stop_label = $Ipt->Label;
                                if ($Ipt->isListType())
                                {
                                    $input_stop_errormsg = "La valeur renseignée ne fait pas partie des options disponibles.";
                                }
                                else
                                {
                                    $input_stop_errormsg = $Ipt->ErrorMsg;
                                }          
                                break;
                            }
                            else
                            {
                                if (isset($post[$post_index]))
                                {
                                    $CustomForms[$tarif_slug]['CustomForm'][$i][$Ipt->Name] = $post[$post_index];
                                }
                            }
                        } // end foreach inputs
                        // Verifier si erreur
                        if ($tarif_inputs_loop_test)
                        {
                            $quantity_tarif_loop_test = true;
                            $quantity_tarif_stop_index = $i;
                            break;
                        }
                    } // end for quantity
                    // Verifier si erreur
                    if ($quantity_tarif_loop_test)
                    {
                        $selected_tarifs_loop_test = true;
                        $selected_tarifs_stop_nom = $Tarif->Nom;
                        break;
                    }
                } // si contient inputs
            } // si tarif existe
        } // end foreach selected_tarifs


        // Verifier si erreur
        if ($selected_tarifs_loop_test)
        {
            return [
                'Return' => false,
                'Label' => $input_stop_label,
                'ErrorMsg' => $input_stop_errormsg,
                'Index' => $quantity_tarif_stop_index,
                'Tarif' => $selected_tarifs_stop_nom
            ];
        }
        else
        {
            return ['Return' => true, 'CustomForms' => $CustomForms];
        }
    }

    public function getBestReduc($session_tarifs, $event)
    {
        $max_reduc = 0;
        $max_reduc_tarif_slug = NULL;
        foreach($session_tarifs as $tarif_slug => $tarif_array)
        {
            $Tarif = $event->getTarifBySlug($tarif_slug);
            $Prix_Adh = $Tarif->Prix_Adh;
            $Prix_nonAdh = $Tarif->Prix_nonAdh;
            if (abs($Prix_Adh - $Prix_nonAdh) > $max_reduc)
            {
                $max_reduc_tarif_slug = $tarif_slug;
            }
        }
        return $max_reduc_tarif_slug;
    }

    public function getPrixTotal($session_content, $event)
    {
        $total = 0;
        if ($session_content['AUTH']['Status'] ) // Réduction(s) ?
        {
            if (!$event->MultiAuth()) // Une seule reduc => la plus avantageuse
            {
                $max_reduc_tarif_slug = $this->getBestReduc($session_content['TARIFS'], $event);

                foreach($session_content['TARIFS'] as $tarif_slug => $tarif_array)
                {
                    $Tarif = $event->getTarifBySlug($tarif_slug);
                    if ($tarif_slug == $max_reduc_tarif_slug)
                    {
                        if ($tarif_array['Quantity'] > 1)
                        {
                            $total += $Tarif->Prix_Adh + ($tarif_array['Quantity'] - 1)*$Tarif->Prix_nonAdh;
                        }
                        else
                        {
                            $total += $Tarif->Prix_Adh;
                        }
                    }
                    else
                    {
                        $total += $tarif_array['Quantity']*$Tarif->Prix_nonAdh;
                    }
                }
            }
            else
            {
                foreach($session_content['TARIFS'] as $tarif_slug => $tarif_array)
                {
                    $total += $tarif_array['Quantity']*$Tarif->Prix_Adh;
                }
            }
            
        }
        else
        {
            foreach($session_content['TARIFS'] as $tarif_slug => $tarif_array)
            {
                $total += $tarif_array['Quantity']*$Tarif->Prix_nonAdh;
            }
        }
        return $total;
    }

    public function getRecapTarifsTable($session_content, $event)
    {
        $recap_prix_table = "<table>";
        $total = 0;
        if ($session_content['AUTH']['Status'] ) // Réduction(s) ?
        {
            if (!$event->MultiAuth()) // Une seule reduc => la plus avantageuse
            {
                $max_reduc_tarif_slug = $this->getBestReduc($session_content['TARIFS'], $event);

                foreach($session_content['TARIFS'] as $tarif_slug => $tarif_array)
                {
                    $Tarif = $event->getTarifBySlug($tarif_slug);
                    if ($tarif_slug == $max_reduc_tarif_slug)
                    {
                        if ($tarif_array['Quantity'] > 1)
                        {
                            $recap_prix_table .= '<tr><th> 1x - '.$Tarif->Nom.' (Prix adhérent)</th><td>'.$Tarif->getPrixEuro("Adh").'</td></tr>';
                            $recap_prix_table .= '<tr><th>'.($tarif_array['Quantity'] - 1).'x - '.$Tarif->Nom.'</th><td>'.$Tarif->getPrixEuro("", ($tarif_array['Quantity'] - 1)).'</td></tr>';
                            $total += $Tarif->Prix_Adh + ($tarif_array['Quantity'] - 1)*$Tarif->Prix_nonAdh;
                        }
                        else
                        {
                            $recap_prix_table .= '<tr><th> 1x - '.$Tarif->Nom.' (Prix adhérent)</th><td>'.$Tarif->getPrixEuro("Adh").'</td></tr>';
                            $total += $Tarif->Prix_Adh;
                        }
                    }
                    else
                    {
                        $recap_prix_table .= '<tr><th>'.$tarif_array['Quantity'].'x - '.$Tarif->Nom.'</th><td>'.$Tarif->getPrixEuro("", $tarif_array['Quantity']).'</td></tr>';
                        $total += $tarif_array['Quantity']*$Tarif->Prix_nonAdh;
                    }
                }
            }
            else
            {
                foreach($session_content['TARIFS'] as $tarif_slug => $tarif_array)
                {
                    $Tarif = $event->getTarifBySlug($tarif_slug);
                    $recap_prix_table .= '<tr><th>'.$tarif_array['Quantity'].'x - '.$Tarif->Nom.' (Prix adhérent)</th><td>'.$Tarif->getPrixEuro("Adh", $tarif_array['Quantity']).'</td></tr>';
                    $total += $tarif_array['Quantity']*$Tarif->Prix_Adh;
                }
            }
            
        }
        else
        {
            foreach($session_content['TARIFS'] as $tarif_slug => $tarif_array)
            {
                $Tarif = $event->getTarifBySlug($tarif_slug);
                $recap_prix_table .= '<tr><th>'.$tarif_array['Quantity'].'x - '.$Tarif->Nom.'</th><td>'.$Tarif->getPrixEuro("", $tarif_array['Quantity']).'</td></tr>';
                $total += $tarif_array['Quantity']*$Tarif->Prix_nonAdh;
            }
        }
        $recap_prix_table .= "<tr><th>Total</th><td>".$total."€</td></tr>";
        $recap_prix_table .= "</table>";
        return ['table' => $recap_prix_table, 'total' => $total];
    }

    public function getRecapFormTable($globalform_post, $event)
    {
        $table = '<table>';

        $Inputs = $event->getInputs();
        foreach ($Inputs as $Ipt)
        {
            if (isset($globalform_post[$Ipt->Name]))
            {
                $table .= '<tr><th>'.$Ipt->Label.'</th><td>'.$globalform_post[$Ipt->Name].'</td></tr>';
            }
            else
            {
                $table .= '<tr><th>'.$Ipt->Label.'</th><td> - </td></tr>';
            }
        }
        $table .= '</table>';
        return $table;
    }

    public function getRecapTarifs($session_content_tarifs, $event)
    {
        $recap = "";
        foreach($session_content_tarifs as $tarif_slug => $tarif_session) // loop tout les tarif_slug selectionnés
        {
            $Tarif = $event->getTarifBySlug($tarif_slug);
            if ($Tarif) // Vérifie que le tarif existe bien
            {
                $Inputs = $Tarif->getInputs();
                if (sizeof($Inputs) > 0) // Vérifie contient des cis
                {
                    for ($i=1; $i <= $tarif_session['Quantity'] ; $i++)
                    { 
                        $recap .= '<hr>'.$Tarif->Nom." -> ".$i; 
                        $recap .= $this->getRecapFormTable($tarif_session['CustomForm'][$i], $Tarif).'<hr>';
                        ?>
                        <hr>
                        <?php
                    } 
                }
                else
                {
                    $recap .= '<hr>'.$Tarif->Nom." -> ".$tarif_session['Quantity'].'<hr>'; 
                }
            }
        }
        return $recap;
    }














    /**
     * INSCRIPTION
     */

    public function getInscription_Raw($ID_inscription)
    {
        $req = $this->_bdd->web()->prepare('SELECT * FROM events_inscriptions WHERE ID_inscription = :ID_inscription');
        $req->execute(['ID_inscription' => $ID_inscription]);
        $req = $req->fetch(PDO::FETCH_ASSOC);
        return $req;
    }

    public function getInscription($ID_inscription)
    {
        $inscription_raw = $this->getInscription_Raw($ID_inscription);

        if ($inscription_raw)
        {
            $billets_IDs = json_decode($inscription_raw['billets_IDs'], true);
        
            $billets_array = [];
            foreach($billets_IDs as $ID_billet)
            {
                $billets_array[] = $this->getBillet($ID_billet);
            }
            
            $inscription = new event_inscription(   $inscription_raw['ID_inscription'],
                                                    $inscription_raw['event_slug'],
                                                    $inscription_raw['Nom'],
                                                    $inscription_raw['Prenom'],
                                                    $inscription_raw['Classe'],
                                                    $inscription_raw['email'],
                                                    $inscription_raw['ID_adherent'],
                                                    json_decode($inscription_raw['CustomInfos'], true),
                                                    $billets_IDs,
                                                    $billets_array,
                                                    json_decode($inscription_raw['paiements_IDs'], true),
                                                    $inscription_raw['total_Prix'],
                                                    $inscription_raw['TimeStamp']     );
            return $inscription;
        }
        else
        {
            return false;
        }
        
    }

    public function createInscription($event, $session, $paiement_type, $auth)
    {
        // Creation de l'array custominfos
        $event_CustomInfos = $session['content']['GLOBALFORM']['post'];
        unset($event_CustomInfos['nom']);
        unset($event_CustomInfos['prenom']);
        unset($event_CustomInfos['classe']);
        unset($event_CustomInfos['email']);
        
        
        $event_slug = $event->getSlug();
        
        $ID_inscription = $this->genID_inscription($event_slug);

        // cb
        $paiement_cb = $paiement_type == "CB";

        // Création des billets et des paiements si cb
        $billets_array = [];
        $billets_IDs = [];
        $paiement_IDs = [];
        if($auth) 
        {
            $ID_adherent = $session['content']['AUTH']['ID_adherent'];

            if (!$event->MultiAuth()) // Une seule reduc => la plus avantageuse
            {
                $max_reduc_tarif_slug = $this->getBestReduc($session['content']['TARIFS'], $event);
                foreach($session['content']['TARIFS'] as $tarif_slug => $tarif_session)
                {
                    
                    $Tarif = $event->getTarifBySlug($tarif_slug);
                    
                    if ($tarif_slug == $max_reduc_tarif_slug) // appliquer la réduction
                    {
                        
                        if ($tarif_session['Quantity'] > 1) // plusieurs billets -> appliquer la réduc au premier seulement
                        {
                            $ID_billet = $this->genID_billet($event_slug, $tarif_slug);
                            $billet =  new event_billet(    $ID_billet,
                                                            $session['content']['GLOBALFORM']['post']['nom'],
                                                            $session['content']['GLOBALFORM']['post']['prenom'],
                                                            $session['content']['GLOBALFORM']['post']['classe'],
                                                            $session['content']['GLOBALFORM']['post']['email'],
                                                            $ID_adherent,
                                                            $event_CustomInfos,
                                                            $Tarif->Nom,
                                                            $Tarif->Slug,
                                                            $tarif_session['CustomForm'][1],
                                                            "ADHERENT",
                                                            $Tarif->Prix_Adh,
                                                            time() );
                            $this->addBillet($event_slug, $ID_inscription, $billet);
                            $billets_IDs[] = $ID_billet;
                            $billets_array[] = $billet;

                            if ($paiement_cb)
                            {
                                $ID_paiement = $this->genID_paiement($event_slug);
                                $this->addPaiement($event_slug, $ID_inscription, $ID_paiement, $billet->ID, $billet->tarif_Prix, $billet->prix_Type);
                                $paiement_IDs[] = $ID_paiement;
                            }

                            for ($i=2; $i <= $tarif_session['Quantity'] ; $i++) // Loop autant de fois le tarif que la quantité selectionnée
                            {
                                $ID_billet = $this->genID_billet($event_slug, $tarif_slug);
                                $billet = new event_billet(     $ID_billet,
                                                                $session['content']['GLOBALFORM']['post']['nom'],
                                                                $session['content']['GLOBALFORM']['post']['prenom'],
                                                                $session['content']['GLOBALFORM']['post']['classe'],
                                                                $session['content']['GLOBALFORM']['post']['email'],
                                                                $ID_adherent,
                                                                $event_CustomInfos,
                                                                $Tarif->Nom,
                                                                $Tarif->Slug,
                                                                $tarif_session['CustomForm'][$i],
                                                                "NON_ADHERENT",
                                                                $Tarif->Prix_nonAdh,
                                                                time() );
                                $this->addBillet($event_slug, $ID_inscription, $billet);
                                $billets_IDs[] = $ID_billet;
                                $billets_array[] = $billet;

                                if ($paiement_cb)
                                {
                                    $ID_paiement = $this->genID_paiement($event_slug);
                                    $this->addPaiement($event_slug, $ID_inscription, $ID_paiement, $billet->ID, $billet->tarif_Prix, $billet->prix_Type);
                                    $paiement_IDs[] = $ID_paiement;
                                }
                            }
                        }
                        else // un seul billet -> appliquer la réduc
                        {
                            $ID_billet = $this->genID_billet($event_slug, $tarif_slug);
                            $billet = new event_billet(     $ID_billet,
                                                            $session['content']['GLOBALFORM']['post']['nom'],
                                                            $session['content']['GLOBALFORM']['post']['prenom'],
                                                            $session['content']['GLOBALFORM']['post']['classe'],
                                                            $session['content']['GLOBALFORM']['post']['email'],
                                                            $ID_adherent,
                                                            $event_CustomInfos,
                                                            $Tarif->Nom,
                                                            $Tarif->Slug,
                                                            $tarif_session['CustomForm'][1],
                                                            "ADHERENT",
                                                            $Tarif->Prix_Adh,
                                                            time() );
                            $this->addBillet($event_slug, $ID_inscription, $billet);
                            $billets_IDs[] = $ID_billet;
                            $billets_array[] = $billet;

                            if ($paiement_cb)
                            {
                                $ID_paiement = $this->genID_paiement($event_slug);
                                $this->addPaiement($event_slug, $ID_inscription, $ID_paiement, $billet->ID, $billet->tarif_Prix, $billet->prix_Type);
                                $paiement_IDs[] = $ID_paiement;
                            }
                        }
                    }
                    else // tarif avec réduction moins avantageuse -> pas de réduc
                    {
                        for ($i=1; $i <= $tarif_session['Quantity'] ; $i++) // Loop autant de fois le tarif que la quantité selectionnée
                        {
                            $ID_billet = $this->genID_billet($event_slug, $tarif_slug);
                            $billet = new event_billet(     $ID_billet,
                                                            $session['content']['GLOBALFORM']['post']['nom'],
                                                            $session['content']['GLOBALFORM']['post']['prenom'],
                                                            $session['content']['GLOBALFORM']['post']['classe'],
                                                            $session['content']['GLOBALFORM']['post']['email'],
                                                            $ID_adherent,
                                                            $event_CustomInfos,
                                                            $Tarif->Nom,
                                                            $Tarif->Slug,
                                                            $tarif_session['CustomForm'][$i],
                                                            "NON_ADHERENT",
                                                            $Tarif->Prix_nonAdh,
                                                            time() );
                            $this->addBillet($event_slug, $ID_inscription, $billet);
                            $billets_IDs[] = $ID_billet;
                            $billets_array[] = $billet;

                            if ($paiement_cb)
                            {
                                $ID_paiement = $this->genID_paiement($event_slug);
                                $this->addPaiement($event_slug, $ID_inscription, $ID_paiement, $billet->ID, $billet->tarif_Prix, $billet->prix_Type);
                                $paiement_IDs[] = $ID_paiement;
                            }
                        }
                    }
                }
            }
            else // si multi auth activé -> réduc pour tout les billets
            {
                foreach($session['content']['TARIFS'] as $tarif_slug => $tarif_session)
                {
                    for ($i=1; $i <= $tarif_session['Quantity'] ; $i++) // Loop autant de fois le tarif que la quantité selectionnée
                    {
                        $ID_billet = $this->genID_billet($event_slug, $tarif_slug);
                        $billet = new event_billet(     $ID_billet,
                                                        $session['content']['GLOBALFORM']['post']['nom'],
                                                        $session['content']['GLOBALFORM']['post']['prenom'],
                                                        $session['content']['GLOBALFORM']['post']['classe'],
                                                        $session['content']['GLOBALFORM']['post']['email'],
                                                        $ID_adherent,
                                                        $event_CustomInfos,
                                                        $Tarif->Nom,
                                                        $Tarif->Slug,
                                                        $tarif_session['CustomForm'][$i],
                                                        "ADHERENT",
                                                        $Tarif->Prix_Adh,
                                                        time() );
                        $this->addBillet($event_slug, $ID_inscription, $billet);
                        $billets_IDs[] = $ID_billet;
                        $billets_array[] = $billet;

                        if ($paiement_cb)
                        {
                            $ID_paiement = $this->genID_paiement($event_slug);
                            $this->addPaiement($event_slug, $ID_inscription, $ID_paiement, $billet->ID, $billet->tarif_Prix, $billet->prix_Type);
                            $paiement_IDs[] = $ID_paiement;
                        }
                    }
                }
            }
        }
        else // si pas authentifié
        {
            $ID_adherent = NULL;

            foreach($session['content']['TARIFS'] as $tarif_slug => $tarif_session)
            {
                for ($i=1; $i <= $tarif_session['Quantity'] ; $i++) // Loop autant de fois le tarif que la quantité selectionnée
                {
                    $ID_billet = $this->genID_billet($event_slug, $tarif_slug);
                    $billet = new event_billet(     $ID_billet,
                                                    $session['content']['GLOBALFORM']['post']['nom'],
                                                    $session['content']['GLOBALFORM']['post']['prenom'],
                                                    $session['content']['GLOBALFORM']['post']['classe'],
                                                    $session['content']['GLOBALFORM']['post']['email'],
                                                    $ID_adherent,
                                                    $event_CustomInfos,
                                                    $Tarif->Nom,
                                                    $Tarif->Slug,
                                                    $tarif_session['CustomForm'][$i],
                                                    "NON_ADHERENT",
                                                    $Tarif->Prix_nonAdh,
                                                    time() );
                    $this->addBillet($event_slug, $ID_inscription, $billet);
                    $billets_IDs[] = $ID_billet;
                    $billets_array[] = $billet;

                    if ($paiement_cb)
                    {
                        $ID_paiement = $this->genID_paiement($event_slug);
                        $this->addPaiement($event_slug, $ID_inscription, $ID_paiement, $billet->ID, $billet->tarif_Prix, $billet->prix_Type);
                        $paiement_IDs[] = $ID_paiement;
                    }
                }
            }
        }

        $total_prix = $this->getPrixTotal($session['content'], $event);

        // paiement si cheque
        if (!$paiement_cb)
        {
            $ID_paiement = $this->genID_paiement($event_slug);
            $this->addPaiement($event_slug, $ID_inscription, $ID_paiement, $billets_IDs, $total_prix, $prix_Type, "CHEQUE");
            $paiement_IDs[] = $ID_paiement;
        }
        // Création de l'inscription
        $inscription = new event_inscription(   $ID_inscription,
                                                $event_slug,
                                                $session['content']['GLOBALFORM']['post']['nom'],
                                                $session['content']['GLOBALFORM']['post']['prenom'],
                                                $session['content']['GLOBALFORM']['post']['classe'],
                                                $session['content']['GLOBALFORM']['post']['email'],
                                                $ID_adherent,
                                                $event_CustomInfos,
                                                $billets_IDs,
                                                $billets_array,
                                                $paiement_IDs,
                                                $total_prix,
                                                time()     );
        $this->addInscription($event_slug, $inscription);

        return $inscription;
    }
















    /**
     * GESTION DES BILLETS
     */

    public function getBillet($ID_billet)
    {
        $req = $this->_bdd->web()->prepare('SELECT * FROM events_billets WHERE ID_billet = :ID_billet');
        $req->execute(['ID_billet' => $ID_billet]);
        $req = $req->fetch(PDO::FETCH_ASSOC);

        if ($req)
        {
            $billet = event_billet::importFromString($req['billet']);
            $billet->setTimeStamp($req['TimeStamp']);
    
            return $billet;
        }
        else
        {
            return false;
        }

        
    }

    public function addBillet($event_slug, $ID_inscription, $billet)
    {
        if ($this->getEvent($event_slug))
        {
            $req = $this->_bdd->web()->prepare('INSERT INTO events_billets (event_slug, ID_inscription, ID_billet, billet) VALUES(:event_slug, :ID_inscription, :ID_billet, :billet)');
            $res = $req->execute([
                'event_slug' => $event_slug,
                'ID_inscription' => $ID_inscription,
                'ID_billet' => $billet->ID,
                'billet' => $billet->exportToString()
            ]);
            return (bool) $res;
        }
        else
        {
            return false;
        }
    }

    public function addInscription($event_slug, $inscription)
    {
        if ($this->getEvent($event_slug))
        {
            $req = $this->_bdd->web()->prepare('INSERT INTO
            events_inscriptions (event_slug, ID_inscription, Nom, Prenom, Classe, email, ID_adherent, CustomInfos, billets_IDs, paiements_IDs, total_Prix)
            VALUES(:event_slug, :ID_inscription, :Nom, :Prenom, :Classe, :email, :ID_adherent, :CustomInfos, :billets_IDs, :paiements_IDs, :total_Prix)');
            $res = $req->execute([
                'event_slug' => $event_slug,
                'ID_inscription' => $inscription->ID,
                'Nom' => $inscription->Nom,
                'Prenom' => $inscription->Prenom,
                'Classe' => $inscription->Classe,
                'email' => $inscription->Email,
                'ID_adherent' => $inscription->ID_adherent,
                'CustomInfos' => json_encode($inscription->event_CustomInfos, JSON_PRETTY_PRINT),
                'billets_IDs' => json_encode($inscription->billets_IDs, JSON_PRETTY_PRINT),
                'paiements_IDs' => json_encode($inscription->paiements_IDs, JSON_PRETTY_PRINT),
                'total_Prix' => $inscription->total_prix
            ]);
            return (bool) $res;
        }
        else
        {
            return false;
        }
    }

    public function genID_billet($event_slug, $tarif_slug)
    {
        $event = substr(strtoupper(str_replace("-", "", $event_slug)), 0, 3);
        $tarif = substr(strtoupper(str_replace("-", "", $tarif_slug)), 0, 3);

        $ID = $event.$tarif.'-'.mt_rand(100000, 999999);

        while ($this->test_ID_billet($ID))
        {
            $ID = $event.$tarif.'-'.mt_rand(100000, 999999);
        }

        return $ID;
    }

    public function test_ID_billet($ID) // Renvoi vrai si existe
    {
        $req = $this->_bdd->web()->prepare('SELECT ID FROM events_billets WHERE ID_billet = :ID_billet');
        $req->execute(['ID_billet' => $ID ]);
        $req = $req->fetch(PDO::FETCH_ASSOC);

        if ($req == false)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public function genID_inscription($event_slug)
    {
        $event = substr(strtoupper(str_replace("-", "", $event_slug)), 0, 6);

        $ID = $event.'-'.mt_rand(10000000, 99999999);

        while ($this->test_ID_inscription($ID))
        {
            $ID = $event.'-'.mt_rand(10000000, 99999999);
        }

        return $ID;
    }

    public function test_ID_inscription($ID) // Renvoi vrai si existe
    {
        $req = $this->_bdd->web()->prepare('SELECT ID FROM events_inscription WHERE ID_inscription = :ID_inscription');
        $req->execute(['ID_inscription' => $ID]);
        $req = $req->fetch(PDO::FETCH_ASSOC);

        if ($req == false)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public function getBilletStatus($ID_billet)
    {
        $req = $bdd->web()->prepare('SELECT Status FROM events_billets_validation WHERE ID_billet = :ID_billet');
        $req->execute(['ID_billet' => $ID_billet]);
        $req = $req->fetch(PDO::FETCH_ASSOC);
        if (isset($req['Status']))
        {
            return $req['Status'];
        }
        else
        {
            // $this->setBilletStatus($ID_billet, "NOT_CHECKED");
            return "NOT_CHECKED";
        }
    }

    public function setBilletStatus($ID_billet, $status = "CHECKED")
    {
        // $req = $this->_bdd->web()->prepare('UPDATE events_billets_validation SET Status = :Status WHERE ID_billet = :ID_billet');
        $req = $this->_bdd->web()->prepare('INSERT INTO events_billets_validation (ID_billet,Status) VALUES (:ID_billet,:Status) ON DUPLICATE KEY UPDATE Status = :Status');
        $res = $req->execute([
            'ID_billet' => $ID_billet,
            'Status' => $ID_paiement
        ]);
        return $res;
    }

    public function autoCheck($ID_billet)
    {
        if ($this->getBilletStatus($ID_billet) == "NOT_CHECKED")
        {
            return $this->setBilletStatus($ID_billet, "CHECKED");
        }
        else
        {
            return false;
        }
    }


    


    /**
     * GESTION DES PAIEMENTS
     */

    public function addPaiement($event_slug, $ID_inscription, $ID_paiement, $ID_billet, $Prix, $Prix_Type, $paiement_Type = "CB")
    {
        if ($this->getEvent($event_slug))
        {
            $req = $this->_bdd->web()->prepare('INSERT INTO events_paiements (event_slug, ID_inscription, ID_billet, ID_paiement, Type, Status, Prix, Prix_Type) VALUES(:event_slug, :ID_inscription, :ID_billet, :ID_paiement, :Type, :Status, :Prix, :Prix_Type)');
            
            $res = $req->execute([
                'event_slug' => $event_slug,
                'ID_inscription' => $ID_inscription,
                'ID_billet' => json_encode($ID_billet, JSON_PRETTY_PRINT),
                'ID_paiement' => $ID_paiement,
                'Type' => $paiement_Type,
                'Status' => "WAITING",
                'Prix' => $Prix,
                'Prix_Type' => $Prix_Type
            ]);
            return (bool) $res;
        }
        else
        {
            return false;
        }
    }

    public function genID_paiement($event_slug)
    {
        $ID = random_pronounceable_word(6)."-".mt_rand(100, 999);

        while ($this->test_ID_paiement($event_slug, $ID))
        {
            $ID = random_pronounceable_word(6)."-".mt_rand(10, 999);
        }

        return $ID;
    }

    public function test_ID_paiement($event_slug, $ID) // Renvoi vrai si existe
    {
        $req = $this->_bdd->web()->prepare('SELECT ID FROM events_paiements WHERE ID_paiement = :ID_paiement AND event_slug = :event_slug');
        $req->execute(['ID_paiement' => $ID, 'event_slug' => $event_slug ]);
        $req = $req->fetch(PDO::FETCH_ASSOC);

        if ($req == false)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public function getPaiement($event_slug, $ID_paiement)
    {
        $req = $this->_bdd->web()->prepare('SELECT * FROM events_paiements WHERE ID_paiement = :ID_paiement AND event_slug = :event_slug');
        $req->execute(['ID_paiement' => $ID_paiement, 'event_slug' => $event_slug ]);
        $req = $req->fetch(PDO::FETCH_ASSOC);

        return $req;
    }

    public function updatePaiement($event_slug, $ID_paiement, $ID_helloasso, $status, $timestamp_validation = false)
    {
        if ($timestamp_validation)
        {
            $req = $this->_bdd->web()->prepare('UPDATE events_paiements
            SET Status = :Status, TimeStamp_validation = NOW(), ID_helloasso = :ID_helloasso
            WHERE event_slug = :event_slug AND ID_paiement = :ID_paiement');
        }
        else
        {
            $req = $this->_bdd->web()->prepare('UPDATE events_paiements
            SET Status = :Status, ID_helloasso = :ID_helloasso
            WHERE event_slug = :event_slug AND ID_paiement = :ID_paiement');
        }

        $req->execute([
            'Status' => $status,
            'ID_helloasso' => $ID_helloasso,
            'event_slug' => $event_slug,
            'ID_paiement' => $ID_paiement
        ]);

    }































    /**
     * 
     * REGISTER SESSION
     * 
     */


    public function register_session_start($event_slug)
    {
        if (isset($_GET['session_id']))
        {
            $get_session_id = $_GET['session_id'];
        }

        if (isset($_SESSION['events']['register']['session']))
        {
            $session_session_id = $_SESSION['events']['register']['session']['id'];
        }
        
        // Get et pas de session => recuperer si possible la session du get
        if (isset($get_session_id) && !isset($session_session_id))
        {
            $session = $this->register_session_get($get_session_id);
            if ($session)
            {
                $today = new DateTime();
                $session_creationdate = new DateTime($session['TimeStamp']);

                $diff = $today->diff($session_creationdate);
                $hours = $diff->h + 24*$diff->d + 24*30*$diff->m;
                
                if ($hours > self::SESSION_VALID_HOURS || $session['event_slug'] != $event_slug)
                {
                    $this->register_session_stop();
                    $session_id = $this->register_session_create($event_slug);
                    $session = ['id' => $session_id, 'content' => [], 'TimeStamp' => date('Y-m-d'), 'event_slug' => $event_slug];
                }
                else
                {
                    $session = ['id' => $session['session_id'], 'content' => $session['content'], 'TimeStamp' => date('Y-m-d'), 'event_slug' => $event_slug];
                }
            }
            else
            {
                $session_id = $this->register_session_create($event_slug);
                $session = ['id' => $session_id, 'content' => [], 'TimeStamp' => date('Y-m-d'), 'event_slug' => $event_slug];
            }
        }
        // si session, on s'en fout du get
        else if (isset($session_session_id))
        {
            $session = $_SESSION['events']['register']['session'];

            $today = new DateTime();
            $session_creationdate = new DateTime($session['TimeStamp']);

            $diff = $today->diff($session_creationdate);
            $hours = $diff->h + 24*$diff->d + 24*30*$diff->m;
            
            if ($hours > self::SESSION_VALID_HOURS || $session['event_slug'] != $event_slug)
            {
                $this->register_session_stop();
                $session_id = $this->register_session_create($event_slug);
                $session = ['id' => $session_id, 'content' => [], 'TimeStamp' => date('Y-m-d'), 'event_slug' => $event_slug];
            }
            else
            {
                $session['TimeStamp'] = date('Y-m-d');
            }
        }
        else
        {
            $session_id = $this->register_session_create($event_slug);
            $session = ['id' => $session_id, 'content' => [], 'TimeStamp' => date('Y-m-d')];
        }

        $_SESSION['events']['register']['session'] = $session;
    }

    public function register_session_create($event_slug)
    {
        $session_id = self::register_session_idgen();
        while ($this->register_session_get($session_id))
        {
            $session_id = self::register_session_idgen();
        }

        $req = $this->_bdd->web()->prepare('INSERT INTO events_session (event_slug, session_id, content) VALUES(:event_slug, :session_id, :content)');
        $res = $req->execute([
            'event_slug' => $event_slug,
            'session_id' => $session_id,
            'content' => serialize([])
        ]);

        return $session_id;
    } 

    private static function register_session_idgen()
    {
        $length = 32;
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }
    
    public function register_session_get($session_id)
    {
        $req = $this->_bdd->web()->prepare('SELECT * FROM events_session WHERE session_id = :session_id');
        $req->execute(['session_id' => $session_id]);
        $req = $req->fetch(PDO::FETCH_ASSOC);
        if ($req == false)
        {
            return false;
        }
        else
        {
            return ['session_id' => $req['session_id'],
                    'event_slug' => $req['event_slug'], 
                    'content' => unserialize($req['content']),
                    'TimeStamp' => $req['TimeStamp']];
        }
    }

    public function register_session_update()
    {
        if (isset($_SESSION['events']['register']['session']))
        {
            $req = $this->_bdd->web()->prepare('UPDATE events_session SET content = :content WHERE session_id = :session_id');
            $req->execute([
                'content' => serialize($_SESSION['events']['register']['session']['content']),
                'session_id' => $_SESSION['events']['register']['session']['id']
            ]);
        }
        else
        {
            return false;
        }
    }

    public function register_session_stop()
    {
        if (isset($_SESSION['events']['register']['session']))
        {
            $session_id = $_SESSION['events']['register']['session']['id'];
            $this->register_session_delete($session_id);
            unset($_SESSION['events']['register']);
        }
    }

    public function register_session_delete($session_id)
    {
        $req = $this->_bdd->web()->prepare('DELETE FROM events_session WHERE session_id = :session_id');
        return $req->execute(['session_id' => $session_id]);
    }




}

?>