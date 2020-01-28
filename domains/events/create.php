<?php
include('../../dependances/class/base.php');
$gestion_events = new gestion_events($bdd, $gestion_adherents);

// Reprise si jamais on recharge la page sans post
if (!isset($_GET['cancel']) && isset($_SESSION['events']['create']) && isset($_SESSION['events']['create']['event_obj']) && isset($_SESSION['events']['create']['step']))
{
    $step = $_SESSION['events']['create']['step'];
}
else if (isset($_GET['cancel']))
{
    unset($_SESSION['events']['create']);
}

// Debuter
if (!isset($step))
{
    $step = "START";
}


/**
 * BACK
 */
if (isset($_GET['back']) && isset($_SESSION['events']['create']['step']) && isset($_SESSION['events']['create']['event_obj']))
{
    if ($_SESSION['events']['create']['step'] == "CUSTOM_INPUTS")
    {
        // retour à START
        $_SESSION['events']['create']['back_event_obj'] = $_SESSION['events']['create']['event_obj'];
        $step = "START";
    }
    else if ($_SESSION['events']['create']['step'] == "CREATION_TARIFS")
    {
        // retour à CUSTOM_INPUTS
        $_SESSION['events']['create']['back_event_obj'] = $_SESSION['events']['create']['event_obj'];
        $step = "CUSTOM_INPUTS";
    }
}


/**
 * PROCESS SERVER SIDE
 */

if (isset($_POST["START"]))
{
    if (isset($_POST["Titre"]) && isset($_POST["Soustitre"]) && isset($_POST["Miniature_slug"]) && isset($_POST["Description"]))
    {
        $event = new event($_POST["Titre"], $_POST["Miniature_slug"], $_POST["Banner_slug"], $_POST["Soustitre"], $_POST["Description"]);

        $_SESSION['events']['create']['event_obj'] = $event;

        $step = "CUSTOM_INPUTS";
    }
}
else if (isset($_POST['CUSTOM_INPUTS'])
        && isset($_POST['MultiAuth'])
        && isset($_POST['MailTemplate'])
        && isset($_POST['ShowConditions_date_start'])
        && isset($_POST['ShowConditions_date_stop'])
        && isset($_POST['ShowConditions_show']))
{
    $event = $_SESSION['events']['create']['event_obj'];

    if (isset($_POST["event_CustomInputs"]) && $_POST["event_CustomInputs"] != "NULL")
    {

        $CustomInputs = event::buildCustomInputsArray($_POST["event_CustomInputs"]);
        $event->setCustomInputs($CustomInputs);
        
    }

    $show = $_POST["ShowConditions_show"] == "true";
    $event_showconditions = new event_showconditions($_POST['ShowConditions_date_start'], $_POST['ShowConditions_date_stop'], $show);

    $MultiAuth = $_POST["MultiAuth"] == "true";
    $event->setOtherInfos($MultiAuth, $_POST['MailTemplate'], $event_showconditions);

    $_SESSION['events']['create']['event_obj'] = $event;

    $step = "CREATION_TARIFS";
}
else if (isset($_POST["ADD_TARIF"]) && isset($_SESSION['events']['create']) && isset($_SESSION['events']['create']['event_obj']))
{
    if (    isset($_POST["Nom"])
            && isset($_POST["Prix_nonAdh"])
            && isset($_POST["Prix_Adh"])
            && isset($_POST["Description"])
            && isset($_POST["Helloasso_nonAdh"])
            && isset($_POST["Helloasso_Adh"])
            && isset($_POST["Helloasso_nonAdh_url"])
            && isset($_POST["Helloasso_Adh_url"]))
    {
        $event = $_SESSION['events']['create']['event_obj'];

        $Nom = htmlspecialchars(strip_tags($_POST["Nom"]));
        $Description = htmlspecialchars(strip_tags($_POST["Description"]));
        $Helloasso_nonAdh = slugify($_POST["Helloasso_nonAdh"]);
        $Helloasso_Adh = slugify($_POST["Helloasso_Adh"]);
        $CustomInputs = event::buildCustomInputsArray($_POST["CustomInputs"]);

        if (!isset($_SESSION['events']['create']['CampaignsNames']) || isset($_GET['refresh_Helloasso_options']))
        {
            $_SESSION['events']['create']['CampaignsNames'] = helloasso::getAllCampaingsNames();
        }
        $Helloasso_CampaingsNames = $_SESSION['events']['create']['CampaignsNames'];

        $tarif = new event_tarif($Nom, $_POST["Prix_nonAdh"], $_POST["Prix_Adh"], $Description, $Helloasso_nonAdh, $Helloasso_Adh, $_POST["Helloasso_nonAdh_url"], $_POST["Helloasso_Adh_url"], $Helloasso_CampaingsNames, $CustomInputs);
        $event->addTarif($tarif);

        $_SESSION['events']['create']['event_obj'] = $event;

        $step = "CREATION_TARIFS";
    }
}
else if (isset($_GET['save']) && $step == "CREATION_TARIFS")
{
    if ($gestion_events->addEvent($_SESSION['events']['create']['event_obj']))
    {
        echo "Saved";
    }
    else
    {
        echo "Error";
    }
    
}

// Stockage de l'étape dans la session
$_SESSION['events']['create']['step'] = $step;

/**
 * ETAPES, USER SIDE
 */
echo "<pre>";
echo(json_encode($_POST, JSON_PRETTY_PRINT));
// echo(json_encode($_SESSION, JSON_PRETTY_PRINT));
echo "</pre>";
?>
<hr>
<a href="?cancel">Annuler</a><br/>
<hr>

<?php

if ($step == "START")
{
    unset($_SESSION['events']['create']['event_obj']);
    ?>

    <script src="https://cdn.ckeditor.com/4.13.0/standard/ckeditor.js"></script>
    <form action="?" method="post">
        <label>Titre</label><input type="text" name="Titre" required="required" value="<?php gestion_events::createEvent_showPrevValues("Titre"); ?>"><br/>

        <label>Soustitre</label><input type="text" name="Soustitre" required="required" value="<?php gestion_events::createEvent_showPrevValues("Soustitre"); ?>"><br/>

        <label>Miniature</label><input type="text" name="Miniature_slug" required="required" value="<?php gestion_events::createEvent_showPrevValues("Miniature_slug"); ?>"><br/>

        <label>Bannière</label><input type="text" name="Banner_slug" required="required" value="<?php gestion_events::createEvent_showPrevValues("Banner_slug"); ?>"><br/>

        <label>Description</label><br/>
        <textarea name="Description" id="Description" required="required"><?php gestion_events::createEvent_showPrevValues("Description"); ?></textarea><script>CKEDITOR.replace( 'Description' );</script><br/>

        <input type="submit" name="START" value="Commencer">
    </form>

    <?php
}
// On a déjà commencé la creation
else if (isset($_SESSION['events']['create']) && isset($_SESSION['events']['create']['event_obj']) && isset($_SESSION['events']['create']['step']))
{
    $event = $_SESSION['events']['create']['event_obj'];
    $event_main_infos = $event->getMainInfos();
    ?>

    <h3>Vous êtes en train de créer: <?php echo $event_main_infos["Titre"]; ?></h3>
    <hr>
    <table>
        <tr> <th>Titre</th><td><?php echo $event_main_infos["Titre"]; ?></td> </tr>
        <tr> <th>Slug</th><td><?php echo $event_main_infos["Slug"]; ?></td> </tr>
        <tr> <th>Soustitre</th><td><?php echo $event_main_infos["Soustitre"]; ?></td> </tr>
        <tr> <th>Description</th><td><?php echo $event_main_infos["Description"]; ?></td> </tr>
        <tr> <th>Miniature</th><td><img src="<?php echo $event_main_infos["Miniature"]; ?>" style="width: 250px"></td> </tr>
        <tr> <th>Bannière</th><td><img src="<?php echo $event_main_infos["Banner"]; ?>" style="width: 250px"></td> </tr>

        <tr> <th>MultiAuth</th><td><?php echo $event_main_infos["MultiAuth"]; ?></td> </tr>
        <tr> <th>MailTemplate</th><td><?php echo $event_main_infos["MailTemplate"]; ?></td> </tr>
        <tr> <th>Activer</th><td><?php echo $event_main_infos["ShowConditions_show"]; ?></td> </tr>
        <tr> <th>Date Debut</th><td><?php echo $event_main_infos["ShowConditions_date_start"]; ?></td> </tr>
        <tr> <th>Date Fin</th><td><?php echo $event_main_infos["ShowConditions_date_stop"]; ?></td> </tr>
    </table>
    <hr>
        <?php echo $gestion_events->getInputs($event); ?>
    <hr>


    <?php
    if ($step == "CUSTOM_INPUTS")
    {
        ?>
        <a href="?back">Retour</a><br/>
        <hr>
        <form action="?" method="post">

            <label for="MultiAuth">MultiAuth</label>
            <input name="MultiAuth" type="radio"  id="MultiAuth-true" value="true" required="required" <?php gestion_events::createEvent_showPrevValues("MultiAuth-true"); ?>><label for="MultiAuth-true" >Oui</label>
            <input name="MultiAuth" type="radio"  id="MultiAuth-false" value="false" required="required" <?php gestion_events::createEvent_showPrevValues("MultiAuth-false"); ?>><label for="MultiAuth-false" >Non</label></br>

            <label>MailTemplate</label><input type="number" name="MailTemplate" required="required" value="<?php gestion_events::createEvent_showPrevValues("MailTemplate"); ?>"><br/>

            <label>Date debut</label><input type="date" name="ShowConditions_date_start" required="required" value="<?php gestion_events::createEvent_showPrevValues("ShowConditions_date_start"); ?>"><br/>
            <label>Date fin</label><input type="date" name="ShowConditions_date_stop" required="required" value="<?php gestion_events::createEvent_showPrevValues("ShowConditions_date_stop"); ?>"><br/>

            <label for="ShowConditions_show">Activer</label>
            <input name="ShowConditions_show" type="radio"  id="ShowConditions_show-true" value="true" required="required" <?php gestion_events::createEvent_showPrevValues("ShowConditions_show-true"); ?>><label for="ShowConditions_show-true" >Oui</label>
            <input name="ShowConditions_show" type="radio"  id="ShowConditions_show-false" value="false" required="required" <?php gestion_events::createEvent_showPrevValues("ShowConditions_show-false"); ?>><label for="ShowConditions_show-false" >Non</label></br>
            
            <p>Les champs suivants sont demandés automatiquement: Nom, Prénom, Classe, Email</p>
            <label>Champs personnalisés</label><br/>
            <textarea name="event_CustomInputs" required="required"><?php gestion_events::createEvent_showPrevValues("event_CustomInputs"); ?></textarea><br/>
            <small>Séparer les champs par des ";". <a href="https://events.bde-bp.fr/create_custominput" target="_blank">Créez des champs ici</a></small></br>
            
            <input type="submit" name="CUSTOM_INPUTS" value="Ajouter des champs personnalisés">

        </form>
        <?php
    }
    else if ($step == "CREATION_TARIFS")
    {
        $tarifsOk = $event->tarifsOk();


        if (!isset($_SESSION['events']['create']['CampaignsNames']) || isset($_GET['refresh_Helloasso_options']))
        {
            $_SESSION['events']['create']['CampaignsNames'] = helloasso::getAllCampaingsNames();
        }
        $Helloasso_CampaingsNames = $_SESSION['events']['create']['CampaignsNames'];
        $Helloasso_options = gestion_events::createEvent_getHelloassoCampaingsSelectOptions($Helloasso_CampaingsNames);



        foreach($event->getTarifs() as $Tarif)
        {
            ?>
                <table>
                    <tr> <th>Nom</th><td><?php echo $Tarif->Nom; ?></td> </tr>
                    <tr> <th>Slug</th><td><?php echo $Tarif->Slug; ?></td> </tr>
                    <tr> <th>Prix Adh</th><td><?php echo $Tarif->getPrixEuro('Adh'); ?></td> </tr>
                    <tr> <th>Prix nonAdh</th><td><?php echo $Tarif->getPrixEuro('nonAdh'); ?></td> </tr>
                    <tr> <th>Description</th><td><?php echo $Tarif->Description; ?></td> </tr>
                    <tr> <th>Helloasso NonAdh</th><td><?php echo $Tarif->Helloasso_nonAdh_Name; ?></td> </tr>
                    <tr> <th>Helloasso Adh</th><td><?php echo $Tarif->Helloasso_Adh_Name; ?></td> </tr>
                    <tr> <th>Helloasso NonAdh url</th><td><?php echo $Tarif->Helloasso_nonAdh_url; ?></td> </tr>
                    <tr> <th>Helloasso Adh url</th><td><?php echo $Tarif->Helloasso_Adh_url; ?></td> </tr>
                    <tr> <th>CustomInputs</th><td><?php echo $gestion_events->getInputs($Tarif); ?></td> </tr>
                </table>
            <hr>
            <?php
        }




        ?>
        <a href="?back">Retour</a><br/>
        <hr>
        <form action="?" method="post">
            <label>Nom</label><input type="text" name="Nom" required="required"><br/>

            <label>Prix nonAdh</label><input type="number" name="Prix_nonAdh" required="required"><br/>
            <label>Prix Adh</label><input type="number" name="Prix_Adh" required="required"><br/>

            <label>Description</label><br/>
            <textarea name="Description" required="required"></textarea><br/>

            <label>HelloAsso_nonAdh url</label><input type="url" name="Helloasso_nonAdh_url" required="required"><br/>
            <label>HelloAsso_Adh url</label><input type="url" name="Helloasso_Adh_url" required="required"><br/>

            <label>HelloAsso_nonAdh</label><select name="Helloasso_nonAdh" required="required"><?php echo $Helloasso_options; ?></select><br/>
            <label>HelloAsso_Adh</label><select name="Helloasso_Adh" required="required"><?php echo $Helloasso_options; ?></select><br/>
            <a href="?refresh_Helloasso_options">Rafraichir la liste</a><br/>



            <label>Champs personnalisés</label><br/>
            <textarea name="CustomInputs"></textarea><br/>
            <small>Séparer les champs par des ";". <a href="https://events.bde-bp.fr/create_custominput" target="_blank">Créez des champs ici</a></small></br>
            
            <input type="submit" name="ADD_TARIF" value="Ajouter un tarif">
        </form>

        <?php

        if ($tarifsOk)
        {
            echo '<a href="?save">Enregistrer</a>';
        }


    }

}
// Ne pas garder en memoire le retour
unset($_SESSION['events']['create']['back_event_obj']);


?>