<?php

include('../../dependances/class/base.php');



if ($gestion_adherents->isRegisteredORConnected() )
{
    $adherent = $_SESSION['Adherent'];



    if (isset($_GET['resetpass']) && isset($_GET['token']) && panel_admin::verifToken($_GET['token']))
    {
        $IDA = $adherent->getIDA();
        
        $genKey = $gestion_adherents->genKey($IDA); //mail

        $link = "https://auth.bde-bp.fr/mail_query?resetpass&key=".$genKey['key']."&IDA=".$IDA;
        $adherent = $gestion_adherents->getAdherent($IDA);
        $gestion_mails->mail_ResetPass($link, $adherent);

        MessagePage("Nous vous avons envoyé un mail pour réinitialiser votre mot de passe.");
        die();
    }

    panel_admin::genToken(); //eviter les repost

    $promo_codes = $gestion_adherents->getPromoCodes();


?>

<!DOCTYPE HTML>
<!--
	Twenty by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>

	<head>
		<title>BDE - PROFIL</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
        <link rel="stylesheet" href="https://assets.bde-bp.fr/css/main.css" />
        <script src="https://kit.fontawesome.com/1fdd8d0755.js" crossorigin="anonymous"></script>
		<noscript>
            <link rel="stylesheet" href="https://assets.bde-bp.fr/css/noscript.css" />
        </noscript>
		<link rel="shortcut icon" href="https://docs.bde-bp.fr/images/statiques/favicon.ico" />
        <script src='https://www.google.com/recaptcha/api.js'></script>
	</head>


	<body class="no-sidebar is-preload">

<?php include("../../dependances/construct/bandeau.php") ?> 

		<div id="page-wrapper">

<?php include("../../dependances/construct/header.php") ?>

<!-- DEBUT -->

<!-- Main -->
<article id="main">

    <header class="special container">
        <span class="icon fa-user"></span>
        <h2><strong>Profil</strong></h2>
        <p>Page du site</p>
        <p>Connecté en tant que <?php echo $adherent->getPNom(); ?></br>
            <a href="https://auth.bde-bp.fr/logout">Déconnexion</a></p>
    </header>

<?php
if ($adherent->getStatus() == adherent::STATUS_ANNULE)
{
?>

    <section class="wrapper style4 container">
        <header>
            <h3>Votre adhésion est annulée</h3>
        </header>
        <div class="content">
            <p>Si c'est une erreur contactez un membre du bureau.</p>
        </div>
    </section>

<?php
}
else
{
?>


    <?php
    if (sizeof($promo_codes) > 0)
    {
    ?>

    <!-- Promo code -->
        <section class="wrapper style4 container">
            <header>
                <h3>Codes promotionnels</h3>
            </header>

            <p>Les codes promotionnels suivants sont <strong><u>RÉSERVÉS AUX ADHÉRENTS</u></strong> et <strong><u>NE DOIVENT PAS ÊTRES COMMUNIQUÉS AUX NON ADHÉRENTS</u></strong>.
            Quand vous utilisez l'un de ces codes pour un évènement HelloAsso <strong>vous devez vous <u>identifier</u> en indiquant votre numéro d'adhérent</strong> quand il vous sera demandé (Votre identifiant: <em><?php echo $adherent->getIDA(); ?></em>),
            sinon votre inscription à l'évènement en question ne sera pas valide et sera <strong>annulée (<u>pas de remboursement possible</u>)</strong>
            si vous ne pouvez pas prouver que vous êtes adhérent (ou payer la différence).<br>
            <br>
            <p style="color: rgb(200,50,50); font-weight: bold; padding: 0; margin: 0; text-align: center;">1 ADHÉRENT = 1 IDENTIFIANT = 1 CODE PROMO = 1 INSCRIPTION<u></u></p><br>
            <br>
            Si vous achetez plusieurs billets, ou que vous vous inscrivez plusieurs fois, <strong><u>UNE SEULE IDENTIFICATION POSSIBLE !</u></strong><br>
            <br>
            Si votre numéro d'adhérent est utilisé plusieurs fois pour vous identifier à un évènement, associé à l'un de ces codes,
            une seule inscription sera validée, et les autres annulées (ou devront être régularisées).<br>
            <br>
            <strong><em>Une inscription annulée ne sera pas remboursée.</em></strong></p>

            <div class="content">
                <table>
                    <tr style="font-weight:bold">
                        <td>Évènement</td>
                        <td>Code</td>
                    </tr>
                    <?php
                    foreach($promo_codes as $code)
                    {
                    ?>
                        <tr>
                            <td><?php echo $code["Event"]; ?></td>
                            <td><?php
                            
                            if ($gestion_adherents->checkPaiement())
                            {
                                echo $code["Code"]; 
                            }
                            else
                            {
                                echo "-";
                            }
                            
                            ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </table>
            </div>

            <?php
            if (!$gestion_adherents->checkPaiement())
            {
                echo '<p style="color: rgb(200,50,50); font-weight: bold">Vous devez payer votre adhésion pour consulter les codes promotionnels. Si vous pensez que c\'est une erreur contactez un membre du bureau.</p>'; 
            }
            ?>
        </section>

    <?php
    }
    ?>


    <!-- Infos -->
        <section class="wrapper style4 container">

            <div style="padding-bottom:0em;" class="content">

                <section>
                    <h3>Tes infos</h3>
                    <p>Voici les informations que nous avons sur toi :</p>
                    <table>
                        <tr style="font-weight:bold">
                            <td>Identifiant</td>
                            <td>Prénom</td>
                            <td>Nom</td>
                            <td>Classe</td>
                            <td>Email</td>
                            <td>Statut</td>
                            <td>Role</td>
                        </tr>
                        <tr>
                            <td><?php echo $adherent->getIDA(); ?></td>
                            <td><?php echo $adherent->getPrenom(); ?></td>
                            <td><?php echo $adherent->getNom(); ?></td>
                            <td><?php echo $adherent->getClasse(); ?></td>
                            <td><?php echo $adherent->getEmail() ?></td>
                            <td><?php echo $adherent->getStatusString(); ?></td>
                            <td><?php echo $adherent->getRoleString(); ?></td>
                        </tr>
                    </table>
                    <p>
                        Si jamais des informations sont incorrectes, contactez un membre du bureau pour des modifications.
                    </p>
                </section>

            </div>

        </section>



    <?php
    if ($gestion_adherents->GotAPass())
    {
    ?>
    <!-- Réinitialisation du mot de passe -->
        <section class="wrapper style4 container">
            <header>
                <h3>Réinitialiser mon mot de passe</h3>
            </header>
            <div class="content">
                <a class="button" href="?resetpass&token=<?php echo panel_admin::getToken(); ?>">Réinitialiser mon mot de passe</a>
                <p>Vous serez déconnecté et vous devrez récréer un mot de passe depuis la section Première Connection.</p>
            </div>
        </section>
    <?php
    }
    ?>

    <!-- Teaser -->
        <section class="wrapper style4 container">
            <header>
                <h3>Bientôt disponible</h3>
            </header>
            <div class="content">
                <p>Bientôt vous pourrez retrouver sur cette page toute vos inscriptions aux différents évènements organisés par le BDE.</p>
            </div>
        </section>

<?php
}
?>




</article>





















<!-- FIN -->
<?php include("../../dependances/construct/footer.php"); ?>

		</div>

		<!-- Scripts -->
			<script src="https://assets.bde-bp.fr/js/jquery.min.js"></script>
			<script src="https://assets.bde-bp.fr/js/jquery.dropotron.min.js"></script>
			<script src="https://assets.bde-bp.fr/js/jquery.scrolly.min.js"></script>
			<script src="https://assets.bde-bp.fr/js/jquery.scrollex.min.js"></script>
			<script src="https://assets.bde-bp.fr/js/browser.min.js"></script>
			<script src="https://assets.bde-bp.fr/js/breakpoints.min.js"></script>
			<script src="https://assets.bde-bp.fr/js/util.js"></script>
			<script src="https://assets.bde-bp.fr/js/main.js"></script>

	</body>
</html>






















<?php
}
else
{
    header('Location: https://auth.bde-bp.fr');
}

?>