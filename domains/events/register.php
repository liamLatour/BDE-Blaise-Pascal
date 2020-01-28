<?php
include('../../dependances/class/base.php');
$gestion_events = new gestion_events($bdd, $gestion_adherents);

if (isset($_GET['cancel']) && isset($_GET['event']))
{
    $gestion_events->register_session_stop();
    header('Location: https://events.bde-bp.fr/register?event='.$_GET['event']);
}


if (isset($_GET['event']))
{
    // eviter de recuperer l'event dans la bdd a chaque fois -> verification de la sanité à la fin
    if (isset($_SESSION['events']['register']['event']))
    {
        $event = $_SESSION['events']['register']['event'];
        if ($event->getSlug() != $_GET['event'])
        {
            $event = $gestion_events->getEvent($_GET['event']);
            $_SESSION['events']['register']['event'] = $event;
        }
    }
    else
    {
        $event = $gestion_events->getEvent($_GET['event']);
        $_SESSION['events']['register']['event'] = $event;
    }

    // Vérification que l'event est actif
    if ($event && $event->actif())
    {
        // demarre la session
        $gestion_events->register_session_start($event->getSlug());
        $session = $_SESSION['events']['register']['session'];

        

        /** ============================================================================================
         * PAIEMENT
         */

        if (isset($session['content']['STEP'])
            && $session['content']['STEP'] == "SELECT_PAIEMENT"
            && $session['content']['RECAP']
            && isset($_GET['go_paiement']))
        {
            // verification sanity de l'event dans la session
            if ($gestion_events->checkSanity($event))
            {
                // Vérification si adhérent -> reauth A FAIRE
                if ($session['content']['AUTH']['Status'])
                {
                    if ($gestion_adherents->isConnected() && $_SESSION['Adherent']->getIDA() == $session['content']['AUTH']['ID_adherent'] && $gestion_events->register_auth($event, $_SESSION['Adherent']->getIDA()))
                    {
                        $ID_adherent = $session['content']['AUTH']['ID_adherent'];
                        $auth = true;
                        $prix_Type = "ADHERENT";
                    }
                    else
                    {
                        $auth_revoq = true;
                    }
                }
                else
                {
                    $ID_adherent = NULL;
                    $auth = false;
                    $prix_Type = "NON_ADHERENT";
                }

                if (isset($auth_revoq) && $auth_revoq)
                {
                    MessagePage("AUTH_REVOQ");
                }
                else
                {
                    if ($_GET['go_paiement'] == "CHEQUE" || $_GET['go_paiement'] == "CB")
                    {
                        $inscription = $gestion_events->createInscription($event, $session, $_GET['go_paiement'], $auth);
                        
                        $gestion_events->register_session_stop();
                        header('Location: https://events.bde-bp.fr/recap?i='.$inscription->ID);
                        die();
                        
                        
                
                    }
                }



            }
            else
            {
                MessagePage("BAD_SANITY");
            }

        }
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        // temporaire
        echo '<pre>';
        var_dump($_SESSION['events']['register']['session']);
        echo '</pre><hr></br></br>';

        // Annuler
        echo '<a href="?event='.$event->getSlug().'&cancel">Annuler</a>';
        echo '<hr>';

        // Pour les différents btn et form
        $link_prefix = "?event=".$event->getSlug()."&session_id=".$session['id'];

        // Informations rapide sur l'event, pour afficher rapidement le titre et autres
        $event_main_infos = $event->getMainInfos();








        




































        /** ============================================================================================
         * BACK
         */
        if (isset($session['content']['STEP']) && isset($_GET['back']) && isset($_GET['back_from']))
        {
            if ($_GET['back_from'] == "AUTH")
            {
                unset($session['content']['AUTH']);
                $session['content']['STEP'] = "PREVIEW";
            }
            else if ($_GET['back_from'] == "GLOBALFORM")
            {
                unset($session['content']['AUTH']);
                $session['content']['STEP'] = "AUTH";
            }
            else if ($_GET['back_from'] == "SELECT_TARIFS")
            {
                unset($session['content']['GLOBALFORM']);
                $session['content']['STEP'] = "GLOBALFORM";
            }
            else if ($_GET['back_from'] == "TARIF_CUSTOM_FORM")
            {
                unset($session['content']['TARIFS']);
                $session['content']['STEP'] = "SELECT_TARIFS";
            }
            else if ($_GET['back_from'] == "RECAP")
            {
                foreach($session['content']['TARIFS'] as $tarif_slug => $content)
                {
                    unset($session['content']['TARIFS'][$tarif_slug]['CustomForm']);
                }
                $session['content']['STEP'] = "TARIF_CUSTOM_FORM";
            }
            else if ($_GET['back_from'] == "SELECT_PAIEMENT")
            {
                unset($session['content']['RECAP']);
                $session['content']['STEP'] = "RECAP";
            }
        }






















        /** ============================================================================================
         * 
         * GESTIONS POSTS
         * 
         */
        if (isset($session['content']['STEP']))
        {
            // GLOBALFORM
            if (isset($_POST['GLOBALFORM']) && $session['content']['STEP'] == "GLOBALFORM")
            {
                $post = $_POST;
                if ($gestion_adherents->isConnected())
                {
                    $adherent = $_SESSION['Adherent'];
                    $post['nom'] = $adherent->getNom();
                    $post['prenom'] = $adherent->getPrenom();
                    $post['email'] = $adherent->getEmail();
                    $post['classe'] = $adherent->getClasse();
                }

                $handle = $gestion_events->handleGlobalForm($event, $post);
                if ($handle['Return'] == true)
                {
                    $session['content']['STEP'] = "SELECT_TARIFS";
                    $session['content']['GLOBALFORM']['post'] = $handle['GlobalForm'];
                }
                else
                {
                    $form_error = 'Le champ "'.$handle['Label'].'" à retourné une erreur :</br>'.$handle['ErrorMsg'];
                }
            }
            // SELECT_TARIFS
            else if (isset($_POST['SELECT_TARIFS']) && $session['content']['STEP'] == "SELECT_TARIFS")
            {
                $Tarifs_Slugs = $event->getTarifsSlugs();
                foreach($Tarifs_Slugs as $slug)
                {
                    if (isset($_POST[$slug]))
                    {
                        if ( abs((int) $_POST[$slug]) > 0 )
                        {
                            $session['content']['TARIFS'][$slug]['Quantity'] = abs((int) $_POST[$slug]);
                        }
                    }
                    else
                    {
                        $select_tarif_error = 'Tout les champs sont obligatoires';
                        break;
                    }
                }
                if (isset($session['content']['TARIFS']) && sizeof($session['content']['TARIFS']) > 0)
                {
                    $session['content']['STEP'] = "TARIF_CUSTOM_FORM";
                }
                else if (!isset($select_tarif_error))
                {
                    $select_tarif_error = 'Vous devez selectionner au moins un tarif.';
                }
            }
            // TARIF_CUSTOM_FORM
            else if (isset($_POST['TARIF_CUSTOM_FORM']) && isset($session['content']['TARIFS']) && $session['content']['STEP'] == "TARIF_CUSTOM_FORM")
            {
                $post = $_POST;

                $handle = $gestion_events->handleTarifsForm($event, $session['content']['TARIFS'], $post);
                if ($handle['Return'] == true)
                {
                    $session['content']['STEP'] = "RECAP";
                    foreach ($handle['CustomForms'] as $tarif_slug => $CustomForm)
                    {
                        $session['content']['TARIFS'][$tarif_slug]['CustomForm'] = $CustomForm['CustomForm'];
                    }
                }
                else
                {
                    $form_error = 'Le champ "'.$handle['Label'].'" à retourné une erreur :</br>'.$handle['ErrorMsg'];
                }
            }
    }







































        /** ============================================================================================
         * USER STEPS SIDE
         */

        // AUTHENTIFICATION
        if (    isset($session['content']['STEP']) // doit avoir commencé
                && (    (isset($_GET['auth']) && $session['content']['STEP'] == "PREVIEW") // vient de l'étape précédente OU
                        || ($session['content']['STEP'] == "AUTH" && !isset($_GET['globalform'])) // simple refresh
                    )
            )
        {
            // Mise à jour de l'étape
            $session['content']['STEP'] = "AUTH";
            $_SESSION['lastpage'] = 'https://events.bde-bp.fr/register.php'.$link_prefix;

            // Retour
            echo '<a href="'.$link_prefix.'&back&back_from='.$session['content']['STEP'].'">Retour</a></br><hr>';

            // Verification de connection 
            if ($gestion_adherents->isConnected())
            {
                if ($gestion_events->register_auth($event, $_SESSION['Adherent']->getIDA()))
                {
                    $session['content']['AUTH']['Status'] = true; // adhérent confirmé, et aucun auth ou multiauth actif
                    $session['content']['AUTH']['ID_adherent'] = $_SESSION['Adherent']->getIDA();
                    ?>
                        <a href="<?php echo $link_prefix; ?>&globalform">Continuer en tant que <?php echo $_SESSION['Adherent']->getPNom(); ?></a>
                        <a href="https://auth.bde-bp.fr/logout">Se deconnecter</a>
                    <?php
                }
                else
                {
                    $session['content']['AUTH']['Status'] = false; // adherent connecté, mais pas bon status ou déjà auth
                    ?>
                        Vous ne pouvez pas vous authentifier pour cet évènement, pour l'une des raisons suivantes:
                        - Votre compte n'a pas le status adhérent.
                        - Vous vous êtes déjà authentifié une fois pour cet évènement et celui-ci n'accepte qu'une seule authentification.
                        <a href="<?php echo $link_prefix; ?>&globalform">Continuer en tant que non adhérent</a>
                    <?php
                }
            }
            else
            {
                $session['content']['AUTH']['Status'] = false; // pas connecté
                ?>

                Vous n'êtes pas connecté. Vous pouvez vous inscrire en tant qu'adhérent à cet évènement.</br>
                <a href="https://auth.bde-bp.fr">Connectez vous ici, vous serez redirigé ici</a></br>
                <a href="<?php echo $link_prefix; ?>&globalform">Continuer sans vous connecter</a>

                <?php
            }
        }

        // GLOBALFORM
        else if (   isset($session['content']['STEP']) // doit avoir commencé
                    &&  (   (isset($_GET['globalform']) && $session['content']['STEP'] == "AUTH") // provient de l'étape précédente OU
                            || ($session['content']['STEP'] == "GLOBALFORM") // simple refresh 
                        )
                )
        {
            // Mise à jour de l'étape
            $session['content']['STEP'] = "GLOBALFORM";

            // Retour
            echo '<a href="'.$link_prefix.'&back&back_from='.$session['content']['STEP'].'">Retour</a></br><hr>';

            // Résumé de l'authentification
            if ($session['content']['AUTH']['Status'])
            {
                $adherent = $gestion_adherents->getAdherent($session['content']['AUTH']['ID_adherent']);
                ?>
                    Vous êtes authentifié en tant que <?php echo $adherent->getInfos() ?>. Certaines informations ne peuvent pas être éditées.
                    <hr>
                <?php
            }
            else
            {
                ?>
                    Vous n'êtes pas authentifié.
                    <hr>
                <?php
            }

            if (isset($form_error))
            {
                echo $form_error."<hr>";
            }

            echo $gestion_events->getGlobalForm($event, $link_prefix);




        }

        // SELECT_TARIFS
        else if (   isset($session['content']['STEP']) && $session['content']['STEP'] == "SELECT_TARIFS") // provient de l'étape précédente avec les verifs
        {

            // Retour
            echo '<a href="'.$link_prefix.'&back&back_from='.$session['content']['STEP'].'">Retour</a></br><hr>';

            // Résumé de l'authentification
            if ($session['content']['AUTH']['Status'])
            {
                $adherent = $gestion_adherents->getAdherent($session['content']['AUTH']['ID_adherent']);
                ?>
                    Vous êtes authentifié en tant que <?php echo $adherent->getInfos() ?>. Certaines informations ne peuvent pas être éditées.
                    <hr>
                <?php
            }
            else
            {
                ?>
                    Vous n'êtes pas authentifié.
                    <hr>
                <?php
            }

            // Affichage des tarifs
            ?>
                <form action="<?php echo $link_prefix; ?>" method="post">
            <?php
            $Tarifs = $event->getTarifs();
            $prix_Type = "";
            if ($session['content']['AUTH']['Status'])
            {
                $prix_Type .= "Adh";
            }
            foreach ($Tarifs as $Trf)
            {
                ?>
                <hr>
                    <?php echo $Trf->Nom; ?></br>
                    <?php echo $Trf->Description; ?></br>
                    <?php echo $Trf->getPrixEuro($prix_Type); ?>
                    <input  name="<?php echo $Trf->Slug; ?>" id="<?php echo $Trf->Slug; ?>" required="required"  type="number" value="0">
                <hr>
                <?php
            }
            if (isset($select_tarif_error))
            {
                echo $select_tarif_error."<hr>";
            }
            ?> 
                    <button type="submit" name="SELECT_TARIFS">Continuer</button>
                </form>
            <?php
        }

        // TARIF_CUSTOM_FORM
        else if (   isset($session['content']['STEP']) && $session['content']['STEP'] == "TARIF_CUSTOM_FORM")
        {
            // Retour
            echo '<a href="'.$link_prefix.'&back&back_from='.$session['content']['STEP'].'">Retour</a></br><hr>';

            // Résumé de l'authentification
            if ($session['content']['AUTH']['Status'])
            {
                $adherent = $gestion_adherents->getAdherent($session['content']['AUTH']['ID_adherent']);
                ?>
                    Vous êtes authentifié en tant que <?php echo $adherent->getInfos() ?>. Certaines informations ne peuvent pas être éditées.
                    <hr>
                <?php
            }
            else
            {
                ?>
                    Vous n'êtes pas authentifié.
                    <hr>
                <?php
            }

            // Résumé des tarifs et affichage du form
            echo '<form action="'.$link_prefix.'" method="post">'."\n";
            foreach($session['content']['TARIFS'] as $tarif_slug => $tarif_session) // loop tout les tarifs selectionnés
            {
                $Tarif = $event->getTarifBySlug($tarif_slug);
                if ($Tarif) // Vérifie que le tarif existe bien
                {
                    $Inputs = $Tarif->getInputs();
                    if (sizeof($Inputs) > 0) // Vérifie contient des cis
                    {
                        ?>
                        <hr><hr>
                        <?php

                        for ($i=1; $i <= $tarif_session['Quantity'] ; $i++)
                        { 
                            echo $Tarif->Nom." -> ".$i; 
                            echo $gestion_events->getTarifForm($Tarif, $i);
                            ?>
                            <hr>
                            <?php
                        }

                        ?>
                        <hr><hr>
                        <?php
                    }
                    else
                    {
                        ?>
                        <hr>
                        <?php echo $Tarif->Nom; ?> -> <?php echo $tarif_session['Quantity']; ?>
                        <hr>
                        <?php
                    }
                }
            }
            ?>

            <button type="submit" name="TARIF_CUSTOM_FORM">Continuer</button>
            </form>

            <?php

        }

        // RECAP
        else if (   isset($session['content']['STEP']) && $session['content']['STEP'] == "RECAP" && !isset($_GET['select_paiement']))
        {
            // Retour
            echo '<a href="'.$link_prefix.'&back&back_from='.$session['content']['STEP'].'">Retour</a></br><hr>';

            // Résumé de l'authentification
            if ($session['content']['AUTH']['Status'])
            {
                $adherent = $gestion_adherents->getAdherent($session['content']['AUTH']['ID_adherent']);
                ?>
                    Vous êtes authentifié en tant que <?php echo $adherent->getInfos() ?>. Certaines informations ne peuvent pas être éditées.
                    <hr>
                <?php
            }
            else
            {
                ?>
                    Vous n'êtes pas authentifié.
                    <hr>
                <?php
            }

            /**
             * RECAP PRIX
             */
            $recap_prix = $gestion_events->getRecapTarifsTable($session['content'], $event);
            echo "<hr>".$recap_prix['table']."<hr>";

            /**
             * RECAP GLOBALFORM
             */
            $recap_globalform_table = $gestion_events->getRecapFormTable($session['content']['GLOBALFORM']['post'], $event);
            echo "<hr>".$recap_globalform_table."<hr>";

            /**
             * RECAP TARIFS & TARIF_CUSTOM_FORM
             */
            $recap_tarifs = $gestion_events->getRecapTarifs($session['content']['TARIFS'], $event);
            echo "<hr>".$recap_tarifs."<hr>";
            
            ?>
                <a href="<?php echo $link_prefix; ?>&select_paiement">Confirmer ces informations</a>
                <small>Il reste encore une étape avant la finalisation de votre inscription et son enregistrement.</small>
            <?php

        }

        // PAIEMENT_SELECT
        else if (   isset($session['content']['STEP']) // doit avoir commencé
                &&  (   (isset($_GET['select_paiement']) && $session['content']['STEP'] == "RECAP") // provient de l'étape précédente OU
                        || ($session['content']['STEP'] == "SELECT_PAIEMENT" && !(isset($_GET['paiement_cheque']) || isset($_GET['paiement_cb']))) // simple refresh 
                    )
                )
        {
            // Mise à jour de l'étape
            $session['content']['STEP'] = "SELECT_PAIEMENT";

            // Confirmation du recap
            $session['content']['RECAP'] = true;
           
            // Retour
            echo '<a href="'.$link_prefix.'&back&back_from='.$session['content']['STEP'].'">Retour</a></br><hr>';

            // Résumé de l'authentification
            if ($session['content']['AUTH']['Status'])
            {
                $adherent = $gestion_adherents->getAdherent($session['content']['AUTH']['ID_adherent']);
                ?>
                    Vous êtes authentifié en tant que <?php echo $adherent->getInfos() ?>. Certaines informations ne peuvent pas être éditées.
                    <hr>
                <?php
            }
            else
            {
                ?>
                    Vous n'êtes pas authentifié.
                    <hr>
                <?php
            }

            /**
             * RECAP PRIX
             */
            $recap_prix = $gestion_events->getRecapTarifsTable($session['content'], $event);
            echo "<hr>".$recap_prix['table']."<hr>";

            /**
             * CHOIX DU PAIEMENT
             */
            ?>
                <a href="<?php echo $link_prefix; ?>&go_paiement=CHEQUE">Payer <i><?php echo $recap_prix['total']; ?>€</i> en un chèque.</a></br>
                <a href="<?php echo $link_prefix; ?>&go_paiement=CB">Payer par carte (tout les tarifs devront être payés séparément)</a>
                <small>Vous ne pourrez pas revenir en arrière. Ceci enregistrera votre inscription</small>
            <?php
        }


        // PREVIEW
        else // if (!isset($session['content']['STEP']) || $session['content']['STEP'] == "PREVIEW")
        {
            // Mise à jour de l'étape
            $session['content']['STEP'] = "PREVIEW";
            ?>

            <h3><?php echo $event_main_infos["Titre"]; ?></h3>
            <hr>
            <table>
                <tr> <th>Titre</th><td><?php echo $event_main_infos["Titre"]; ?></td> </tr>
                <tr> <th>Soustitre</th><td><?php echo $event_main_infos["Soustitre"]; ?></td> </tr>
                <tr> <th>Description</th><td><?php echo $event_main_infos["Description"]; ?></td> </tr>
                <tr> <th>Miniature</th><td><img src="<?php echo $event_main_infos["Miniature"]; ?>" style="width: 250px"></td> </tr>
            </table>
            <hr>
            <a href="<?php echo $link_prefix; ?>&auth">Commencer</a>

            <?php
        }








































        
        /** ============================================================================================
         * Mise à jour de la session, à éviter à la dernière étape car on doit détruire la session
         */
        $_SESSION['events']['register']['session'] = $session;
        $gestion_events->register_session_update();

    }
    else
    {
        $gestion_events->register_session_stop();
    }
}
else
{
    $gestion_events->register_session_stop();
}






?>