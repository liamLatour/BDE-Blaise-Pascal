<?php
include('./class/base.php');



$show_form = true;
$show_validation = false;
$show_confirmation = false;

$actif = (bool) settings::p('forum')['actif'];

$today = new DateTime();
$datedebut = new DateTime((string) settings::p('forum')['date_debut']);
$datefin  = new DateTime((string) settings::p('forum')['date_fin']);

if ($today->getTimestamp() >= $datedebut->getTimestamp() && $today->getTimestamp() <= $datefin->getTimestamp())
{
    $datecheck = true;
}
else
{
    $datecheck = false;
}



if (isset($_POST['send']))
{  
    $verifPost = verifPost($_POST, $gestion_adherents, $bdd);
    if ($verifPost === true)
    {
        $show_form = false;
        $show_validation = true;

        $_SESSION['POST'] = $_POST;

        if ($_SESSION['POST']['ida'] == '')
        {
            $_SESSION['POST']['ida'] = NULL;
        }
        else
        {
            $_SESSION['POST']['ida'] = (int) $_SESSION['POST']['ida'];
        }
    }
    else
    {
        $erreurs = $verifPost;
    }
}
elseif (isset($_POST['valid']) && $_POST['valid'] == 'o' && isset($_SESSION['POST']))
{
    $post = (array) $_SESSION['POST'];
    $verifPost = verifPost($post, $gestion_adherents, $bdd);
    if ($verifPost === true)
    {

        $req = $bdd->registre()->prepare("INSERT INTO forum (Nom, Prenom, IDA, Classe) VALUES(:Nom, :Prenom, :IDA, :Classe)");
        $res = $req->execute([
            'Nom' => $post['nom'],
            'Prenom' => $post['prenom'],
            'IDA' => $post['ida'],
            'Classe' => $post['classe']
        ]);

        if ($res)
        {
            $inscription_infos = [
                'nom' => $post['nom'],
                'prenom' => $post['prenom'],
                'classe' => $post['classe'],
                'ida' => $post['ida']
            ];

            unset($_SESSION['POST']);
            
            $show_form = false;
            $show_confirmation = true;

            gestion_logs::Log($_SESSION['IP'], log::TYPE_EVENT, 'forum-inscrit', serialize($inscription_infos));
        }
        else
        {
            gestion_logs::Log($_SESSION['IP'], log::TYPE_ERROR, 'forum', 'BDD_INSCRIPTION');
            $erreurs[] = "Une erreur de Base De Données et survenue, si le problème persiste vous pouvez contacter un administrateur.";
        }
    }
    else
    {
        $erreurs = $verifPost;
    }
    
}


function verifPost(array $post, gestion_adherents $gestion_adherents, bdd $bdd)
{
    if  (
            !(
                isset($post['nom'])
                && isset($post['prenom'])
                && isset($post['adh'])
                && isset($post['classe'])
            )
        )
    {
        $erreurs[] = 'Le nom, le prenom, et la classe sont requis.';
    }

    if (isset($post['nom']) && !$gestion_adherents->verifyPNom($post['nom']))
    {
        $erreurs[] = 'Le nom doit faire entre 3 et 30 caractère, et ne contenir que des lettres, tirets, espaces, et ces caractères (àéèëêïîç).';
    }
    if (isset($post['prenom']) && !$gestion_adherents->verifyPNom($post['prenom']))
    {
        $erreurs[] = 'Le prénom doit faire entre 3 et 30 caractère, et ne contenir que des lettres, tirets, espaces, et ces caractères (àéèëêïîç).';
    }

    if  (
            isset($post['nom'])
            && isset($post['prenom'])
            && $gestion_adherents->verifyPNom($post['nom'])
            && $gestion_adherents->verifyPNom($post['prenom'])
            && !uniPnom((string) $post['nom'], (string) $post['prenom'], $bdd)
        )
    {
        $erreurs[] = 'Il y a déjà une inscription enregistrée avec ce nom et ce prenom, si c\'est une erreur, contactez un administrateur.';
    }

    if  (
            $post['adh'] == 'oui'
            &&  (
                    $post['ida'] == ''
                    || !isset($post['ida'])
                )
        )
    {
        $erreurs[] = 'Si vous avez coché que vous êtes adhérent, veuillez renseigner votre identifiant.';
    }

    if (
            $post['adh'] == 'oui'
            &&  $post['ida'] != ''
            && isset($post['ida'])
            && !$gestion_adherents->getAdherent((int) $post['ida'])
        )
    {
        $erreurs[] = 'Aucun adherent avec cet identifiant n\'existe.';
    }
    // elseif  (
    //             $post['adh'] == 'oui'
    //             &&  $post['ida'] != ''
    //             && isset($post['ida'])
    //             &&  !(
    //                     strtoupper(stripAccentsSep($gestion_adherents->getAdherent((int) $post['ida'])->getNom())) == strtoupper(stripAccentsSep($post['nom']))
    //                     && strtoupper(stripAccentsSep($gestion_adherents->getAdherent((int) $post['ida'])->getPrenom())) == strtoupper(stripAccentsSep($post['prenom']))
    //                 )
    //         )
    // {
    //     $erreurs[] = 'Les informations que vous avez entrez ne correspondent pas avec l\'adhérent enregistré. Vérifiez l\'orthographe du Nom et du Prénom.';
    // }

    if  (  
            isset($post['classe'])
            && !in_array($post['classe'], settings::p('classes_forum'))
        )
    {
        $erreurs[] = 'La classe que vous avez renseigné n\'existe pas.';
    }

    if (!isset($erreurs))
    {
        return true;
    }
    else
    {
        return $erreurs;
    }
}


// Renvoi true si unique, false sinon
function uniPNom(string $nom, string $prenom, bdd $bdd)
{
    $req = $bdd->registre()->prepare('SELECT * FROM forum WHERE Nom = :Nom AND Prenom = :Prenom');
    $req->execute([
        'Nom' => $nom,
        'Prenom' => $prenom
    ]);
    $req = $req->fetch(PDO::FETCH_ASSOC);

    return !(bool) $req;
}

?>



<!DOCTYPE html>
<html lang="fr">

<head>
    <!-- Required meta tags-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Inscription au forum">
    <meta name="author" content="Colorlib">
    <meta name="keywords" content="Inscription au forum">

    <!-- Title Page-->
    <title>BDE BP - Inscription au forum</title>
    <link rel="shortcut icon" href="../images/favicon.ico" />

    <!-- Icons font CSS-->
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <!-- Font special for pages-->
    <link href="https://fonts.googleapis.com/css?family=Poppins:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Vendor CSS-->
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">

    <!-- Main CSS-->
    <link href="css/main.css" rel="stylesheet" media="all">

    
</head>

<body>
    <div class="page-wrapper bg-gra-02 p-t-130 p-b-100 font-poppins">
        <div class="wrapper wrapper--w680">
            <div class="card card-4">
                <div class="card-body">
                    <h1 class="title">Inscription au forum</h1>

<?php

if (!$actif)
{

?>


<p>L'inscription au forum a été désactivée.</p>



<?php

}
elseif (!$datecheck)
{

?>

<p>L'inscription au forum n'est plus disponible, elle l'était entre le <strong><?php echo $datedebut->format('d-m-Y').'</strong> et le <strong>'.$datefin->format('d-m-Y'); ?></strong>.</p>

<?php

}
elseif ($show_form)
{
    err::print($erreurs);

?>
                    <form method="POST">
                        <div class="row row-space">
                            <div class="col-2">
                                <div class="input-group">
                                    <label class="label">Nom</label>
                                    <input class="input--style-4" type="text" name="nom" required value="<?php
                                        if (isset($_POST['nom'])) { echo $_POST['nom']; }
                                        elseif (isset($_SESSION['POST']['nom'])) { echo $_SESSION['POST']['nom']; }
                                    ?>">
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="input-group">
                                    <label class="label">Prénom</label>
                                    <input class="input--style-4" type="text" name="prenom" required value="<?php
                                        if (isset($_POST['prenom'])) { echo $_POST['prenom']; }
                                        elseif (isset($_SESSION['POST']['prenom'])) { echo $_SESSION['POST']['prenom']; }
                                    ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row row-space">
                            <div class="col-2">
                                <div class="input-group">
                                    <label class="label">Je suis adhérent</label>
                                    <div class="p-t-10">
                                        <label class="radio-container m-r-45">Oui
                                            <input type="radio" name="adh" value="oui" 
                                            <?php
                                                if (isset($_POST['adh']) && $_POST['adh'] == "oui") { echo 'checked="checked"'; }
                                                elseif (isset($_SESSION['POST']['adh']) && $_SESSION['POST']['adh'] == "oui") { echo 'checked="checked"'; }
                                            ?>>
                                            <span class="checkmark"></span>
                                        </label>
                                        <label class="radio-container">Non
                                            <input type="radio" name="adh" value="non" 
                                            <?php
                                                if (!(isset($_POST['adh']) && $_POST['adh'] == "oui") && !(isset($_SESSION['POST']['adh']) && $_SESSION['POST']['adh'] == "oui")) { echo 'checked="checked"'; }
                                            ?>>
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="input-group">
                                    <label class="label">Numéro d'adhérent</label>
                                    <div class="input-group-icon">
                                        <input class="input--style-4" type="number" name="ida" value="<?php
                                        if (isset($_POST['ida'])) { echo $_POST['ida']; }
                                        elseif (isset($_SESSION['POST']['ida'])) { echo $_SESSION['POST']['ida']; }
                                    ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="input-group">
                            <label class="label">Classe</label>
                            <div class="rs-select2 js-select-simple select--no-search">
                                <select name="classe" required>
                                    <option disabled="disabled" <?php
                                    if (!(isset($_POST['classe'])) || isset($_SESSION['POST']['classe'])) { echo 'selected="selected"'; }
                                    ?>>Choisir votre classe</option>

<?php

foreach (settings::p('classes_forum') as $classe)
{
    if (isset($_POST['classe']) && $_POST['classe'] == $classe)
    {
        $ckd = ' selected="selected" ';
    }
    elseif (isset($_SESSION['POST']['classe']) && $_SESSION['POST']['classe'] == $classe)
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
                                <div class="select-dropdown"></div>
                            </div>
                        </div>
                        <div class="p-t-15">
                            <button class="btn btn--radius-2 btn--green" type="submit" name="send" value="send">Envoyer</button><br>
                            <small>Vous pourrez vérifier vos informations avant de confirmer.</small>
                        </div>
                    </form>

<?php

}
elseif ($show_validation && isset($_SESSION['POST']))
{

?>

<h3>Voulez-vous valider ces informations ?</h3>
<br>

<p style="font-size: 1.4em">
    <strong>Prénom:</strong> <?php echo $_SESSION['POST']['prenom'];?> <br>
    <strong>Nom:</strong> <?php echo $_SESSION['POST']['nom'];?> <br>
<?php
    if (strlen($_SESSION['POST']['ida']) > 0)
    {
        echo "<strong>Identifiant d'adherent:</strong> ".$_SESSION['POST']['ida']."<br>";
    }
?>
    <strong>Classe:</strong> <?php echo $_SESSION['POST']['classe'];?>
</p>

<div class="p-t-15">
    <form method="post">
        <button class="btn btn--radius-2 btn--green" name="valid" value="o" type="submit">Valider</button>
        <button class="btn btn--radius-2 btn--red" name="cancel" value="o" type="submit">Annuler</button>
    </form>
</div>



<?php

}
elseif ($show_confirmation && isset($inscription_infos))
{

?>

<h3>Votre inscription a bien été enregistrée</h3>
<br>

<p style="font-size: 1.4em">
    Les informations suivantes ont été enregistrées dans notre base de données: <br>
    <strong>Prénom:</strong> <?php echo $inscription_infos['prenom'];?> <br>
    <strong>Nom:</strong> <?php echo $inscription_infos['nom'];?> <br>
<?php
    if (strlen($inscription_infos['ida']) > 0)
    {
        echo "<strong>Identifiant d'adherent:</strong> ".$inscription_infos['ida']."<br>";
    }
?>
    <strong>Classe:</strong> <?php echo $inscription_infos['classe'];?>
<p style="font-size: 1.4em">
    Il vous faut maintenant régler votre participation pour confirmer votre inscritpion
     soit par carte en utilisant le module Hello Asso ci-dessous,
     soit par chèque à l'ordre "BDE de Blaise Pascal", en indiquant au dos votre Numéro d'adhérent si vous l'êtes,
     ou juste votre nom et prénom sinon. <br>
</p>
<br>

<div style="text-align: center;">
    <iframe id="haWidgetButton" src="https://www.helloasso.com/associations/bde-de-blaise-pascal/paiements/inscription-au-forum-2019/widget-bouton" style="border: none;"></iframe>
</div>

<?php

}

?>

                </div>
            </div>
        </div>
    </div>

    <!-- Jquery JS-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <!-- Vendor JS-->
    <script src="vendor/select2/select2.min.js"></script>
    <script src="vendor/datepicker/moment.min.js"></script>
    <script src="vendor/datepicker/daterangepicker.js"></script>

    <!-- Main JS-->
    <script src="js/global.js"></script>

</body><!-- This templates was made by Colorlib (https://colorlib.com) -->

</html>
<!-- end document-->