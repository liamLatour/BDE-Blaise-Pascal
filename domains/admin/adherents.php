<?php

include('./content/panel_base.php');

if (isset($show_panel) && $show_panel && $panel_admin->functionAllowed(panel_admin::FCN_VIEWADHERENTS))
{


$page = 'ADHERENTS';

$show = 'ALL';

$edit_perm =    ($panel_admin->functionAllowed(panel_admin::FCN_EDITADHERENTSTATUS) ||
                $panel_admin->functionAllowed(panel_admin::FCN_EDITADHERENTROLE) ||
                $panel_admin->functionAllowed(panel_admin::FCN_RESETADHERENTPASS) ||
                $panel_admin->functionAllowed(panel_admin::FCN_EDITADHERENTINFOS))
                && $panel_admin->functionAllowed(panel_admin::FCN_VIEWIDA);


if (isset($_GET['adhesion_param']))
{
    header('Location: ./parametres?edit&name=adhesion');
}
// Paramètres d'adhésion
else if (isset($_POST['adhesion']))
{
    if (isset($_POST['date_debut']) && isset($_POST['date_fin']))
    {
        $date_debut = date("d-m-Y", strtotime($_POST['date_debut']));
        $date_fin = date("d-m-Y", strtotime($_POST['date_fin']));
        $actif = (bool) isset($_POST['actif']);


        if(!($panel_admin->editAdhesion($date_debut, $date_fin, $actif) === true))
        {
            $erreur_adhesion = "Une erreur est survenue, les paramètres d'adhesion n'ont pas pu être mis à jour,
            vous pouvez tenter de passer par l'onglet paramètres.";
        }
        else
        {
            $confirm = "Les modifications ont correctement été effectuées.";
        }
    }
}
// Paiement
else if (isset($_POST['paiement']) && $_FILES['paiement_file'] && $panel_admin->functionAllowed(panel_admin::FCN_UPDATESTATUSPAIEMENT))
{
    // On va calculer le temps d'execution de cette fonction
    
    // Script start
    // $rustart = getrusage();

    // Script
    $paiement_file = file_get_contents($_FILES['paiement_file']['tmp_name']);
    $paiement_file = preg_replace('/\.$/', '', $paiement_file); //Remove dot at end if exists
    $aiement_file = str_replace(array("\r", "\n", " "), '', $paiement_file); //Supprime les retour a la ligne et espaces
    $paiement_liste = explode(',', $paiement_file); //split string into array seperated by ', '

    $total = sizeof($paiement_liste);
    $done = 0;
    $edit_paiement_error = "";
    foreach($paiement_liste as $IDA) //loop over values
    {
        if ((int) $IDA >= 1000 && (int) $IDA <= 9999)
        {
            $adherent = $gestion_adherents->getAdherent($IDA);
            if ($adherent)
            {
                $status = $adherent->getStatus();
                if ($status == adherent::STATUS_INSCRIT)
                {
                    if ($gestion_adherents->editStatusIDA((int) $IDA, adherent::STATUS_ADHERENT))
                    {
                        $done++;
                        $gestion_mails->mail_ConfirmationPaiementAdhesion($gestion_adherents->getAdherent($IDA));
                    }
                    else
                    {
                        $edit_paiement_error .= $IDA." - <i>CANT_EDIT_STATUS</i> ;<br>";
                    }
                }
                else if ($status == adherent::STATUS_ADHERENT)
                {
                    $edit_paiement_error .= $IDA." - <i>STATUS_ADHERENT</i> ;<br>";
                }
                else if ($status == adherent::STATUS_ANNULE)
                {
                    $edit_paiement_error .= $IDA." - <i>STATUS_ANNULE</i> ;<br>";
                }
                else
                {
                    $edit_paiement_error .= $IDA." - <i>STATUS_ERROR</i> ;<br>";
                }
            }
            else
            {
                $edit_paiement_error .= $IDA." - <i>EXISTE_PAS</i> ;<br>";
            }
        }
        else
        {
            $edit_paiement_error .= $IDA." - <i>BAD_IDA</i> ;<br>";
        }
    }


    // Script end
    // function rutime($ru, $rus, $index)
    // {
    //     return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
    //     -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
    // }
    // $ru = getrusage();
    // $cptime = rutime($ru, $rustart, "utime");
    // $syscall = rutime($ru, $rustart, "stime");

    if ($edit_paiement_error == "")
    {
        $confirm =  "Vous avez confirmé le paiement de ".$total." adhérents sans erreurs.";
    }
    else
    {
        $confirm =  "Vous avez confirmé le paiement de ".$done."/".$total." adhérents.<br><div class=\"alert bg-danger mt-2\"><strong>Erreurs:</strong><br>".$edit_paiement_error."</div>";
    }
}
else if (isset($_POST['cancel_non_adh']) && $panel_admin->functionAllowed(panel_admin::FCN_UPDATESTATUSPAIEMENT))
{
    if ($panel_admin->verifToken($_POST['cancel_non_adh_token']))
    {
        $cancel_non_adh = $panel_admin->cancel_non_adh();
        if (err::c($cancel_non_adh))
        {
            if ($cancel_non_adh['done'] == $cancel_non_adh['total'])
            {
                $confirm = "Vous avez annulé ".$cancel_non_adh['done']."/".$cancel_non_adh['total']." adhérents sans erreurs.";
            }
            else
            {
                $erreur_cancel_non_adh = "";
                foreach($cancel_non_adh['erreurs'] as $erreur)
                {
                    $erreur_cancel_non_adh .= $erreur[0]." - ".$erreur[1].";<br>";
                }
                
                $confirm = "Vous avez annulé ".$cancel_non_adh['done']."/".$cancel_non_adh['total']." adhérents.<br><div class=\"alert bg-danger mt-2\"><strong>Erreurs:</strong><br>".$erreur_cancel_non_adh."</div>";
            }
        }
        else
        {
            $erreur_cancel_non_adh = "Vous n'avez pas le droit de faire ça.";  
        }
    }
    else
    {
        $erreur_cancel_non_adh = "Token erroné";  
    }
    // $adherent = $gestion_adherents->getAdherent($_GET['cancel_non_adh']);
    // if ($adherent && $adherent->getStatus() == adherent::STATUS_INSCRIT)
    // {
    //     if ($gestion_adherents->editStatusIDA((int) $_GET['cancel_non_adh'], adherent::STATUS_ANNULE))
    //     {
    //         $gestion_mails->mail_Annulation($adherent);
    //     }
    //     else
    //     {
    //         $cancel_non_adh_error = "CANT_EDIT_STATUS";           
    //     }
    // }
    // else
    // {
    //     $cancel_non_adh_error = "EXISTE_PAS | STATUS!=INSCRIT";           
    // }

    if (isset($erreur_cancel_non_adh))
    {
        $erreur = $erreur_cancel_non_adh;
    }
}
// Recherche d'adhérents
else if (isset($_POST['search']))
{

    $query_array = [];

    if (isset($_POST['nom']) && strlen($_POST['nom']) > 0)
    {
        $query_array['Nom'] = $_POST['nom'];
    }
    if (isset($_POST['ida']) && strlen($_POST['ida']) > 0)
    {
        $query_array['IDA'] = $_POST['ida'];
    }
    if (isset($_POST['classe']) && $_POST['classe'] != '-')
    {
        $query_array['Classe'] = $_POST['classe'];
    }
    if (isset($_POST['role']) && $_POST['role'] != '-')
    {
        $query_array['Role'] = $_POST['role'];
    }
    if (isset($_POST['status']) && $_POST['status'] != '-')
    {
        $query_array['Status'] = $_POST['status'];
    }

    if (sizeof($query_array) > 0)
    {
        $results = $panel_admin->newSearchAdherents($query_array);
    }
    else
    {
        $results = err::e(err::WRONG_INFOS);
    }


    if (err::c($results))
    {
        if (sizeof($results) == 0)
        {
            $erreur_search = "Aucun resultat pour cette recherche.";
        }
        else
        {
            $_SESSION['panel_admin']['adherents']['results'] = $results;
        }
    }
    else
    {
        $erreur_search = "Vous devez renseigner au moins un paramètre pour effectuer une recherche.";
    }
}
// Export de la recherche
else if (isset($_GET['export']) && isset($_SESSION['panel_admin']['adherents']['results']))
{
    gestion_logs::Log($_SESSION['IP'], log::TYPE_ADMIN, 'adherents/export', '');
    $panel_admin->exportAdherents($_SESSION['panel_admin']['adherents']['results']);
    die();
}
// Editer un adhérent
else if (isset($_GET['edit']) && $panel_admin->isAdherent($_GET['edit']) && $edit_perm)
{
    $show = 'EDIT';
    $panel_admin->genToken();

    $adherent = $gestion_adherents->getAdherent($_GET['edit']);

    $infos['IDA'] = $_GET['edit'];  
    $infos['Nom'] = $adherent->getNom();
    $infos['Prenom'] = $adherent->getPrenom();
    $infos['Classe'] = $adherent->getClasse();
    $infos['Email'] = $adherent->getEmail();
    $infos['Status'] = $adherent->getStatus();
    $infos['Role'] = $adherent->getRole();

}
// Vérification des informations
else if (isset($_POST['Edit']) && $panel_admin->isAdherent($_POST['Edit']) && $edit_perm)
{
    $adherent = $panel_admin->genEditAdherent($_POST);
    $verif = $gestion_adherents->verifyAdherent($adherent);
    if (err::c($verif))
    {
        $show = 'EDIT_CONFIRM';
        $panel_admin->genToken();
        $_SESSION['panel_admin']['adherents']['edit'] = $adherent;
    }
    else
    {
        $show = 'EDIT';
        $panel_admin->genToken();

        switch($verif)
        {
            case $verif == err::BFORM_EMAIL:
                $erreur = "Le format de l'Adresse Mail n'est pas correct.";
                break;

            case $verif == err::BFORM_STATUS:
                $erreur = "Le Status n'est pas correct.";
                break;

            case $verif == err::BFORM_ROLE:
                $erreur = "Le Role n'est pas correct.";
                break;

            case $verif == err::BFORM_NOM:
                $erreur = "Le format du Nom n'est pas correct.";
                break;

            case $verif == err::BFORM_PRENOM:
                $erreur = "Le format du Prénom n'est pas correct.";
                break;

            case $verif == err::BFORM_CLASSE:
                $erreur = "La Classe n'est pas correcte.";
                break;

            default:
                $erreur = "Une erreur inconnue est survenue";
                break;
        }

        $infos['IDA'] = $_POST['edit']; 
        if (isset($_POST['Nom']))       { $infos['Nom'] = $_POST['Nom']; }          else { $infos['Nom'] = ''; }
        if (isset($_POST['Prenom']))    { $infos['Prenom'] = $_POST['Prenom']; }    else { $infos['Prenom'] = ''; }
        if (isset($_POST['Classe']))    { $infos['Classe'] = $_POST['Classe']; }    else { $infos['Classe'] = ''; }
        if (isset($_POST['Email']))     { $infos['Email'] = $_POST['Email']; }      else { $infos['Email'] = ''; }
        if (isset($_POST['Status']))    { $infos['Status'] = $_POST['Status']; }    else { $infos['Status'] = ''; }
        if (isset($_POST['Role']))      { $infos['Role'] = $_POST['Role']; }        else { $infos['Role'] = ''; }



    }
}
// Confirmation et modification de l'adherent
else if (isset($_GET['confirm']) && $panel_admin->verifToken($_GET['confirm']) && isset($_SESSION['panel_admin']['adherents']['edit']) && $edit_perm)
{
    $adherent = $_SESSION['panel_admin']['adherents']['edit'];
    $verif = $gestion_adherents->verifyAdherent($adherent);
    if (err::c($verif))
    {
        gestion_logs::Log($_SESSION['IP'], log::TYPE_ADMIN, 'adherents/edit', $gestion_adherents->getAdherent($adherent->getIDA())->getArray());
        $gestion_adherents->updateAdherent($adherent);
        $confirm = "Vos modifications apportées à <strong>".$adherent->getIDA()." - ".$adherent->getPNom()."</strong> ont bien été enregistrées.";
    }
}
// Annulation de confirmation
else if (isset($_GET['cancel']) && isset($_SESSION['panel_admin']['adherents']['edit']) && $edit_perm)
{
    $show = 'EDIT';

    $adherent = $_SESSION['panel_admin']['adherents']['edit'];

    $infos['IDA'] = $adherent->getIDA();
    $infos['Nom'] = $adherent->getNom();
    $infos['Prenom'] = $adherent->getPrenom();
    $infos['Classe'] = $adherent->getClasse();
    $infos['Email'] = $adherent->getEmail();
    $infos['Status'] = $adherent->getStatus();
    $infos['Role'] = $adherent->getRole();
}
// Reinitialiser un mdp, demande de confirmation
else if (isset($_GET['resetpass'])
        && $panel_admin->isAdherent($_GET['resetpass'])
        && $panel_admin->functionAllowed(panel_admin::FCN_RESETADHERENTPASS))
{
    $_SESSION['panel_admin']['adherents']['resetpass'] = $_GET['resetpass'];

    $show = 'WARNING_RESETPASS';
}
// Reinitialiser un mdp
else if (isset($_GET['resetpass_confirm'])
        && $panel_admin->verifToken($_GET['resetpass_confirm'])
        && isset($_SESSION['panel_admin']['adherents']['resetpass'])
        && $panel_admin->functionAllowed(panel_admin::FCN_RESETADHERENTPASS))
{
    if (err::c($panel_admin->resetAdherentPass($_SESSION['panel_admin']['adherents']['resetpass'])))
    {
        $IDA = $_SESSION['panel_admin']['adherents']['resetpass'];
        gestion_logs::Log($_SESSION['IP'], log::TYPE_ADMIN, 'adherents/resetpass', $IDA);
        $confirm = "Vous avez réinitialisé le mot de passe de <strong>".$IDA." - ".$gestion_adherents->getAdherent($IDA)->getPNom()."</strong>";
    }
    else
    {
        $show = 'EDIT';
        $erreur = "Une erreur est survenue.";
    }
}
// Supprimer un adh, demande de confirmation
else if (isset($_GET['delete'])
        && $panel_admin->isAdherent($_GET['delete'])
        && $panel_admin->functionAllowed(panel_admin::FCN_DELETEADHERENT))
{
    $_SESSION['panel_admin']['adherents']['delete']['IDA'] = $_GET['delete'];
    $_SESSION['panel_admin']['adherents']['delete']['PNom'] = $gestion_adherents->getAdherent($_GET['delete'])->getPNom();

    $show = 'WARNING_DELETE';
}
// Supprimer un adh
else if (isset($_GET['delete_confirm'])
        && $panel_admin->verifToken($_GET['delete_confirm'])
        && isset($_SESSION['panel_admin']['adherents']['delete'])
        && $panel_admin->functionAllowed(panel_admin::FCN_DELETEADHERENT))
{
    if (err::c($panel_admin->deleteAdherent($_SESSION['panel_admin']['adherents']['delete']['IDA'])))
    {
        $IDA = $_SESSION['panel_admin']['adherents']['delete']['IDA'];
        $PNom = $_SESSION['panel_admin']['adherents']['delete']['PNom'];
        $confirm = "Vous avez supprimé <strong>".$IDA." - ".$PNom."</strong>";
    }
    else
    {
        $show = 'EDIT';
        $erreur = "Une erreur est survenue.";
    }
}
// retour, raffiche les results
else if (isset($_GET['back']) || isset($_GET['confirm']))
{
    if (isset($_SESSION['panel_admin']['adherents']['results']))
    {
        $results = $_SESSION['panel_admin']['adherents']['results'];
    }
}

if ($show == 'ALL')
{
    $panel_admin->genToken();
}



?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="https://docs.bde-bp.fr/images/statiques/favicon.ico" />
        <title>BDE BP - ADMIN PANEL - ADHERENTS</title>
        <!-- Bootstrap -->
        <link href="https://assets.bde-bp.fr/css/bootstrap.css" rel="stylesheet">
        <!-- Icons -->
        <script src="https://kit.fontawesome.com/1fdd8d0755.js" crossorigin="anonymous"></script>
        <!-- Edition html -->
        <script src="https://cdn.ckeditor.com/4.13.0/standard/ckeditor.js"></script>
    </head>
<body>

    <?php include('./content/nav.php'); ?>

    <div class="container mt-5 pt-5">
   
        <?php include('./content/alert_display.html'); ?>










<?php
if ($show == 'ALL')
{
?>

        <?php
        if (isset($confirm))
        {
        ?>
            <div class="alert alert-success">
                <?php echo $confirm; ?>
            </div>
        <?php
        }
        ?>

        <?php
        if (isset($erreur))
        {
        ?>
                
                <div class="alert alert-danger">
                    <?php echo $erreur; ?>
                </div>
                
        <?php
        }
        ?>

        <?php
        if ($panel_admin->functionAllowed(panel_admin::FCN_EDITSETTINGS))
        {
        ?>

        <!-- ADHESION -->
        <div>
		  
            <h2>Adhésion</h2>

            
            
            <?php
            if (isset($erreur_adhesion))
            {
            ?>
                    
                    <div class="alert alert-danger">
                        <?php echo $erreur_adhesion; ?>
                    </div>
                    
            <?php
            }
            ?>
                        
            <form class="row" method="post">
              
                <div class="form-group form-check col-md-4 mt-auto text-center">
                    <input type="checkbox" class="form-check-input form-control-lg" id="actif" name="actif" <?php
                    if (settings::p('adhesion')['actif'])
                    {
                        echo 'checked';
                    }
                    ?>>
                    <label class="form-check-label form-control-lg" for="actif">Activer l'adhésion</label>
                </div>
              
                <div class="form-group col-md-4">
                    <label for="Date_Debut">Date activation</label>
                    <input type="date" class="form-control" name="date_debut" id="date_debut" value="<?php
                    echo date("Y-m-d", strtotime(settings::p('adhesion')['date_debut']));
                    ?>">
                </div>
              
                <div class="form-group col-md-4">
                    <label for="Date_Fin">Date fin</label>
                    <input type="date" class="form-control" name="date_fin" id="date_fin" value="<?php
                    echo date("Y-m-d", strtotime(settings::p('adhesion')['date_fin']));
                    ?>">
                </div>

                <div class="form-group col-md-6 mt-auto pt-2">
                    <a class="btn btn-info btn-block" href="?adhesion_param" target="_blank">Accéder aux paramètres</a>
                </div>
              
                <div class="form-group col-md-6 mt-auto pt-2">
                    <button type="submit" class="btn btn-success btn-block" name="adhesion">Mettre à jour</button>
                </div>
              
            </form>
            
        </div>

        <hr class="my-3">

        <?php
        // Fin section FCN_EDITSETTINGS
        }





        if ($panel_admin->functionAllowed(panel_admin::FCN_UPDATESTATUSPAIEMENT))
        {
        ?>

        <!-- CHANGER LE STATUS APRES PAIEMENT -->
        <div>
		  
            <h2>Changer le status (inscrit -> adhérent) d'un grand nombre d'Adhérent</h2>
            <p>Ces adhérents seront <u>notifiés par mail</u> de la confirmation de leur paiement. Crédits restants: <?php echo $gestion_mails->getCredits(); ?></p>

                        
            <form class="row" method="post" enctype="multipart/form-data">
              
                <div class="form-group col-md-6">
                <label for="paiement_file">Fichier contenant la liste des IDA correspondant aux adhérents qui ont payés<br>
                (Séparés par une virgule <i>ex: 1234,5678,9101</i> | édition rapide de texte: <a href="https://bit.ly/2NeSHEe" target="_blank">ici</a>)</label>
                    <div class="custom-file p-0">
                        <input type="file" class="custom-file-input" id="paiement_file" name="paiement_file">
                        <label class="custom-file-label" for="paiement_file">Liste des paiements</label>
                    </div>
                </div>
              
                <div class="form-group col-md-6 mt-auto pt-2">
                    <button type="submit" class="btn btn-success btn-block" name="paiement">Mettre à jour ces adhérents</button>
                </div>
              
            </form>
            
        </div>

        <hr class="my-3">

        <!-- ANNULER LES INSCRITS -->
        <div>
		  
            <h2>Annuler les inscriptions sans paiement</h2>
            <p>Ces adhérents seront <u>notifiés par mail</u> de l'annulation de leur adhésion. Crédits restants: <?php echo $gestion_mails->getCredits(); ?></p>

            <?php
            if (isset($erreur_cancel_non_adh))
            {
            ?>
                    
                    <div class="alert alert-danger">
                        <?php echo $erreur_cancel_non_adh; ?>
                    </div>
                    
            <?php
            }
            ?>            

            <form class="row" method="post">
              
                <div class="form-group col-md-6">
                    <label for="cancel_non_adh_token">Entrez ce token pour confirmer: <strong><?php echo $panel_admin->getToken(); ?></strong></label>
                    <input type="text" class="form-control" id="cancel_non_adh_token" name="cancel_non_adh_token" placeholder="Token de confirmation">
                </div>
              
                <div class="form-group col-md-6 mt-auto pt-2">
                    <button type="submit" class="btn btn-success btn-block" name="cancel_non_adh">Annuler les status inscrit</button>
                </div>
              
            </form>
            
        </div>

        <hr class="my-3">

        <?php
        // Fin section FCN_UPDATESTATUSPAIEMENT
        }


        if ($panel_admin->functionAllowed(panel_admin::FCN_VIEWADHERENTS))
        {
        ?>

        <!-- LISTE DES ADHERENTS -->
        <div>
		  
            <h2>Liste des adhérents</h2>
          
          
            <!-- <div class="alert alert-warning">
                <strong>Attention:</strong> Rechercher tout les adhérents peut prendre du temps et peut faire planter votre navigateur.
            </div> -->

            <!-- <div class="alert alert-secondary">
                Vous ne pouvez rechercher qu'à partir d'un seul paramètre.<br>
                <small>Le premier paramètre en partant de la gauche sera pris en compte pour la recherche, laisser les autres vides pour eviter les problèmes.</small>
            </div> -->

            <?php
            if (isset($erreur_search))
            {
            ?>
                    
                    <div class="alert alert-danger">
                        <?php echo $erreur_search; ?>
                    </div>
                    
            <?php
            }
            ?>
            
            <!-- RECHERCHE -->
            <form class="row" method="post" action="?">
                      
                <div class="form-group col-md-3">
                    <label for="nom">Nom</label>
                    <input type="text" class="form-control" name="nom" id="nom" placeholder="Nom">
                </div>

                <div class="form-group col-md-3">
                    <label for="nom">Identifiant</label>
                    <input type="number" class="form-control" name="ida" id="ida" placeholder="Identifiant">
                </div>
              
                <div class="form-group col-md-3">
                    <label for="classe">Classe</label>
                    <select class="form-control" name="classe" id="classe">
                        <option>-</option>

<?php

foreach (settings::p('gestion_adherents')['classes'] as $classe)
{
    echo '<option>'.$classe.'</option>';
}

?>
                    </select>
                </div>
              
                <div class="form-group col-md-3">
                    <label for="status">Status</label>
                    <select class="form-control" name="status" id="status">
                        <option>-</option>
                        <option value="<?php echo adherent::STATUS_INSCRIT; ?>">INSCRIT</option>
                        <option value="<?php echo adherent::STATUS_ADHERENT; ?>">ADHERENT</option>
                        <option value="<?php echo adherent::STATUS_ANNULE; ?>">ANNULE</option>
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label for="role">Role</label>
                    <select class="form-control" name="role" id="role">
                        <option>-</option>
                        <option value="<?php echo adherent::ROLE_ADHERENT; ?>">ADHERENT</option>
                        <option value="<?php echo adherent::ROLE_CA; ?>">CA</option>
                        <option value="<?php echo adherent::ROLE_BUREAU; ?>">BUREAU</option>
                        <option value="<?php echo adherent::ROLE_ADMIN; ?>">ADMIN</option>
                    </select>
                </div>
              
                <div class="form-group col-md mt-auto pt-2">
                    <button type="submit" class="btn btn-primary btn-block" name="search">Rechercher</button>
                </div>
              
            </form>
          

            <?php
            if (!isset($results))
            {
            ?>
          
            <div class="alert alert-secondary">
                Faites une recherche pour afficher des adhérents
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                </div>
<?php
//if ($panel_admin->functionAllowed(panel_admin::FCN_CLEARREGISTRE))
if (false)
{
?>




                <div class="col-md-8 text-md-right">
                    <a class="btn btn-outline-danger text-danger">Vider le registre</a>
                </div>
<?php
}
?>
            </div>
          
            <?php
            }
            else if (is_array($results) && sizeof($results) > 0)
            {
            ?>


            <div class="alert alert-secondary">
                <?php echo sizeof($results); ?> résultat(s)
            </div>
          
            <div class="row mb-3">

<?php

if ($panel_admin->functionAllowed(panel_admin::FCN_EXPORTADH))
{

?>


                <div class="col-md-6">
                    <a class="btn btn-info btn-block" target="_blank" href="?export">Exporter la recherche</a>
                    <small>Il peut être nécessaire de convertir le fichier csv pour le lire correctement.</small>
                </div>

<?php
}

//if ($panel_admin->functionAllowed(panel_admin::FCN_CLEARREGISTRE))
if (false)
{
?>




                <div class="col-md-6 text-md-right">
                    <a class="btn btn-outline-danger text-danger">Vider le registre</a>
                </div>
<?php
}
?>
            </div>

            <!-- TABLEAU DES RESULTATS -->
          
            <table class="table table-striped">
                <thead>
                    <tr>
                        <?php 
                            if ($panel_admin->functionAllowed(panel_admin::FCN_VIEWIDA))
                            {
                                echo '<th scope="col">IDA</th>';
                            }
                        ?>
                        <th scope="col">Classe</th>
                        <th scope="col">Nom</th>
                        <th scope="col">Prenom</th>
                        <th scope="col">Status</th>
                        <th scope="col">Role</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>

<?php
foreach($results as $res)
{
?>
                    <tr>
                        <?php
                            if ($panel_admin->functionAllowed(panel_admin::FCN_VIEWIDA))
                            {
                        ?>
                                <th scope="row"><?php echo $res['IDA']; ?></th>
                        <?php
                            }
                        ?>
                        <td><?php echo $res['Classe']; ?></td>
                        <td><?php echo $res['Nom']; ?></td>
                        <td><?php echo $res['Prenom']; ?></td>
                        <td><?php echo adherent::getStatusShortStringFromInt($res['Status']); ?></td>
                        <td><?php echo adherent::getRoleShortStringFromInt($res['Role']); ?></td>
                        <?php
                        if ($edit_perm)
                        {
                        ?>
                            <td class="p-1 text-right"><a class="btn btn-info" href="?edit=<?php echo $res['IDA']; ?>">Editer <?php echo $res['IDA']; ?></a></td>
                        <?php
                        }
                        ?>
                    </tr>
<?php
}
?>
                </tbody>
            </table>

            <?php
            }
            ?>
            
        </div>

        <?php
        // Fin section FCN_VIEWADHERENTS
        }
        ?>















<?php
// Fin section show = all
}
else if ($show == 'EDIT' && $edit_perm)
{
    
?>
















        <!-- EDITION -->
        <div>
            
            <h2>Edition</h2>
            
            <div class="alert alert-info">
                Vous éditez <strong><?php
                echo $infos['IDA']." - ".$gestion_adherents->getAdherent($infos['IDA'])->getPNom(); 
                ?></strong> 
            </div>
            
            <div class="row mb-3">
                <div class="col-md-10"></div>
                <div class="col-md-2">
                    <a class="btn btn-secondary btn-block" href="?back">Retour</a>
                </div>
            </div>
            
    <?php
    if (isset($erreur))
    {
    ?>
            
            <div class="alert alert-danger">
                <?php echo $erreur; ?>
            </div>
            
    <?php
    }
    ?>
            
            
            <form method="post" action="?">

<?php
if ($panel_admin->functionAllowed(panel_admin::FCN_EDITADHERENTINFOS))
{
?>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="Nom">Nom</label>
                        <input type="text" class="form-control" name="Nom" id="Nom" placeholder="Nom" value="<?php echo $infos['Nom']; ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="Prenom">Prénom</label>
                        <input type="text" class="form-control" name="Prenom" id="Prenom" placeholder="Prénom" value="<?php echo $infos['Prenom']; ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="Classe">Classe</label>
                        <select class="form-control" id="Classe" name="Classe" required>
                            <?php
                            foreach (settings::p('gestion_adherents')['classes'] as $classe)
                            {
                                if (strtoupper($infos['Classe']) == strtoupper($classe))
                                {
                                    $ckd = ' selected="selected" ';
                                }
                                else
                                {
                                    $ckd = '';
                                }
                                echo '<option value="'.$classe.'"'.$ckd.'>'.ucfirst($classe).'</option>';
                            }
                            ?>
                    </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="Email">Adresse Mail</label>
                        <input type="email" class="form-control" id="Email" name="Email" placeholder="Email" value="<?php echo $infos['Email']; ?>">
                    </div>
                </div>

<?php
}
?>
                <div class="form-row">

<?php
if ($panel_admin->functionAllowed(panel_admin::FCN_EDITADHERENTSTATUS))
{
?>


                    <div class="form-group col-md-6">
                        <label for="Status">Status</label>
                        <select class="form-control" id="Status" name="Status" required>
                            <option value="<?php echo adherent::STATUS_INSCRIT; ?>" <?php if ($infos['Status'] == adherent::STATUS_INSCRIT) { echo 'selected'; } ?>>INSCRIT</option>
                            <option value="<?php echo adherent::STATUS_ADHERENT; ?>" <?php if ($infos['Status'] == adherent::STATUS_ADHERENT) { echo 'selected'; } ?>>ADHERENT</option>
                            <option value="<?php echo adherent::STATUS_ANNULE; ?>" <?php if ($infos['Status'] == adherent::STATUS_ANNULE) { echo 'selected'; } ?>>ANNULE</option>
                        </select>
                    </div>

<?php
}
if ($panel_admin->functionAllowed(panel_admin::FCN_EDITADHERENTROLE))
{
?>

                    <div class="form-group col-md-6">
                        <label for="Role">Role</label>
                        <select class="form-control" id="Role" name="Role" required>
                            <option value="<?php echo adherent::ROLE_ADHERENT; ?>" <?php if ($infos['Role'] == adherent::ROLE_ADHERENT) { echo 'selected'; } ?>>ADHERENT</option>
                            <option value="<?php echo adherent::ROLE_CA; ?>" <?php if ($infos['Role'] == adherent::ROLE_CA) { echo 'selected'; } ?>>CA</option>
                            <option value="<?php echo adherent::ROLE_BUREAU; ?>" <?php if ($infos['Role'] == adherent::ROLE_BUREAU) { echo 'selected'; } ?>>BUREAU</option>
                            <option value="<?php echo adherent::ROLE_ADMIN; ?>" <?php if ($infos['Role'] == adherent::ROLE_ADMIN) { echo 'selected'; } ?>>ADMIN</option>
                        </select>
                    </div>

<?php
}
?>

                </div>
                <div class="row m-0">
                    <button type="submit" class="btn btn-success col-md-3" name="Edit" value="<?php echo $infos['IDA']; ?>">Mettre à jour</button>
                    <?php
                    if ($panel_admin->functionAllowed(panel_admin::FCN_RESETADHERENTPASS))
                    {
                    ?>
                        <a class="btn btn-outline-warning col-md-3 mx-auto" href="?resetpass=<?php echo $infos['IDA']; ?>">Réinitialiser le mot de passe</a>
                    <?php
                    }
                    ?>
                    <?php
                    if ($panel_admin->functionAllowed(panel_admin::FCN_DELETEADHERENT))
                    {
                    ?>
                        <a class="btn btn-outline-danger col-md-3" href="?delete=<?php echo $infos['IDA']; ?>">Supprimer l'adhérent</a>
                    <?php
                    }
                    ?>
                </div>
            </form>
            
        </div>














<?php
// Fin section show = edit
}
else if ($show == 'EDIT_CONFIRM' && $edit_perm && isset($_SESSION['panel_admin']['adherents']['edit']))
{
    $adherent = $_SESSION['panel_admin']['adherents']['edit'];
?>
















        <!-- CONFIRMATION D'EDITION -->
        <div>
            
            <h2>Edition</h2>
            
            <div class="alert alert-info">
                Vous éditez <strong><?php
                echo $adherent->getIDA()." - ".$adherent->getPNom(); 
                ?></strong> 
            </div>
            
    <?php
    if (isset($erreur))
    {
    ?>
            
            <div class="alert alert-danger">
                <?php echo $erreur; ?>
            </div>
            
    <?php
    }
    ?>
            
            <div class="border border-success py-5 px-2 mb-3">
                <div class="container">
                    <h1 class="display-4 font-weight-bold text-success">Vos modifications</h1>
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th scope="row">Nom</th>
                                <td><?php echo $adherent->getNom(); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Prénom</th>
                                <td><?php echo $adherent->getPrenom(); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Email</th>
                                <td><?php echo $adherent->getEmail(); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Classe</th>
                                <td><?php echo $adherent->getClasse(); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Status</th>
                                <td><?php echo $adherent->getStatusStringShort(); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Role</th>
                                <td><?php echo $adherent->getRoleStringShort(); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="btn-group">
                        <a class="btn btn-success" href="?confirm=<?php echo $panel_admin->getToken(); ?>">Confirmer ces modification</a>
                        <a class="btn btn-danger" href="?cancel">Annuler</a>
                    </div>
                    
                </div>
            </div>
        </div>












<?php
// Fin section show = edit_confirm
}
else if ($show == 'WARNING_RESETPASS' && $edit_perm)
{
    $adherent = $gestion_adherents->getAdherent($_SESSION['panel_admin']['adherents']['resetpass']);
?>




        <div class="border border-warning py-5 px-2 mb-3">
		  	<div class="container">
				<h1 class="display-4 font-weight-bold text-warning">Attention</h1>
                <p>Vous êtes sur le point de <span class="bg-danger px-2 py-1 m-2 font-weight-bold">reinitialiser le mot de passe</span>
                de <?php echo $adherent->getIDA()." - ".$adherent->getPNom(); ?></p>
				
                <div class="btn-group">
                    <a class="btn btn-warning" href="?resetpass_confirm=<?php echo $panel_admin->getToken(); ?>">Continuer</a>
                    <a class="btn btn-danger" href="?cancel">Annuler</a>
                </div>
		  	</div>
        </div>
        

        <?php
// Fin section show = edit_confirm
}
else if ($show == 'WARNING_DELETE' && $edit_perm)
{
    $IDA = $_SESSION['panel_admin']['adherents']['delete']['IDA'];
    $PNom = $_SESSION['panel_admin']['adherents']['delete']['PNom'];
?>




        <div class="border border-warning py-5 px-2 mb-3">
		  	<div class="container">
				<h1 class="display-4 font-weight-bold text-warning">Attention</h1>
                <p>Vous êtes sur le point de <span class="bg-danger px-2 py-1 m-2 font-weight-bold">supprimer</span>
                <?php echo $IDA." - ".$PNom; ?> de la base de données des adhérents.</p>
                
                <div class="btn-group">
				    <a class="btn btn-warning" href="?delete_confirm=<?php echo $panel_admin->getToken(); ?>">Continuer</a>
				    <a class="btn btn-danger" href="?cancel">Annuler</a>
                </div>
		  	</div>
        </div>
        
 



<?php
}
?>






    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) --> 
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>
<?php
}
else
{
echo "Le panel ne peut pas s'afficher, peut être que vous n'avez pas la permission d'être ici.";
}
?>