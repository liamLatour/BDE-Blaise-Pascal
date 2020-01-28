<?php

include('../../dependances/class/base.php');

// $settings = settings::p('adhesion');

// $actif = (bool) $settings['actif'];

// $today = new DateTime();
// $datedebut = new DateTime((string) $settings['date_debut']);
// $datefin  = new DateTime((string) $settings['date_fin']);

// if ($today->getTimestamp() >= $datedebut->getTimestamp() && $today->getTimestamp() <= $datefin->getTimestamp())
// {
//     $datecheck = true;
// }
// else
// {
//     $datecheck = false;
// }

if (isset($_GET['ov_adh']))
{
    $ov_adh_token = $_GET['ov_adh'];
    $publication = gestion_adherents::publicationAdhesion() || $gestion_adherents->privateAdhesionToken($ov_adh_token);
}
else
{
    $publication = gestion_adherents::publicationAdhesion();
}

$lienHelloAsso = settings::p('adhesion')['lien_helloasso'];


$url = "https://www.google.com/recaptcha/api/siteverify";
$public_key = "6LeBF3sUAAAAABFHrrRKv4_OMy4jzjZHO7kWIOYA";
$private_key = "6LeBF3sUAAAAALptB0OxezByVAYQ6rBautLqcr0m";


if  (  
    $publication
    && isset($_POST['FormName'])
    && $_POST['FormName'] == 'adhesion'
    && !$gestion_adherents->isRegisteredORConnected()
    && isset($_POST['g-recaptcha-response'])
    )
{
    $response_key = $_POST['g-recaptcha-response'];
    $response = file_get_contents($url.'?secret='.$private_key.'&response='.$response_key);
    $response = json_decode($response);

    if ($response->success == 1)
    {
        gestion_logs::Log($_SESSION['IP'], log::TYPE_COMPTE, 'adherent/adhesion-askinscription', $_POST);

        $inscription = $gestion_adherents->inscriptionHandler($_POST);
        if ($inscription === true)
        {
            gestion_logs::Log($_SESSION['IP'], log::TYPE_COMPTE, 'adherent/adhesion-inscrit', $_SESSION);
            $_SESSION['adhesion']['allowcancel'] = $_SESSION['Adherent']->getIDA();
            $confirmation = true;

            if (isset($ov_adh_token))
            {
                $gestion_adherents->confirm_privateAdhesionToken($ov_adh_token, $_SESSION['Adherent']->getIDA());
            }
        }
        else
        {
            $erreurs = $inscription;
        }
    }
    else
    {
        $erreurs[] = "Le Recapcha n'est pas correct.";
    }
}
else if (
        // $publication
        isset($_GET['cancel'])
        && $gestion_adherents->isRegisteredORConnected() 
        && $gestion_adherents->isAdherent($_GET['cancel'])
        && $_SESSION['Adherent']->getIDA() == $_GET['cancel']
        && isset($_SESSION['adhesion']['allowcancel'])
        && $_SESSION['adhesion']['allowcancel'] == $_GET['cancel']
        )
{
    gestion_logs::Log($_SESSION['IP'], log::TYPE_COMPTE, 'adherent/adhesion-cancel', $_GET['cancel']);
    $gestion_adherents->delAdherent($_GET['cancel']);
    $gestion_adherents->logout();
}


if ($gestion_adherents->isRegisteredORConnected() && !isset($confirmation))
{
    header('Location: https://adherent.bde-bp.fr');
}




?>

<!DOCTYPE HTML>
<!--
	Twenty by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>

	<head>
		<title>BDE - Adhésion</title>
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



		    <!-- Main -->
				<article id="main">

					<header class="special container">
            
						<span class="icon fa-user"></span>
						<h2><strong>Adhésion</strong></h2>

<?php
    if ($publication && !isset($confirmation))
    { 
?>

                        <p> Allez viens, viens ! On est bien ... Allez, rejoins nous, on est déjà <strong><?php echo $gestion_adherents->CountAdherents(); ?></strong> !</p>

					</header>


					<!-- Modalités -->
						<section class="wrapper style4 container">

							<!-- Content -->
								<div class="content">
									<section>

										<header id="ici">
                                            <h3>Modalités d'adhésion</h3>
										</header>


										<p>Si tu veux savoir pourquoi ton adhésion compte, tu peux aller consulter la page d'<a href="https://bde-bp.fr">Accueil</a>.</p>

										<p>L'adhésion au BDE est ouverte à partir de la <b>rentrée</b> et jusqu'à <b>fin septembre</b> (la date exacte sera précisée ulterieurement). D'autres sessions pourront éventuellement être ouvertes en fonction des demandes et du nombre d'adhérents déjà inscrits.</p>
                                        <p>Toute adhésion, peu importe la filière et la date de l'inscription, s'élève à un montant de <b>10€</b> (dix euros). Cette somme est à régler soit par carte bancaire,
                                        à l'aide du formulaire HelloAsso disponible à la fin de ton adhésion, soit par chèque, à déposer dans la boîte aux lettres de l'association. Toute adhésion doit être
                                        réglée dans les <b>quinze jours</b> suivants ton inscription. Au-delà de cette date, ton adhesion sera annulée.</p>

                                        <p><span style="text-decoration: underline;font-weight:bold">Si tu t'es déjà inscrit</span> sur cette page et que tu cherches juste le lien pour régler l'adhésion en ligne,
                                        <a href="<?php echo $lienHelloAsso; ?>">c'est cadeau</a>.</p>

                                        <p>En cas de rétraction, <strong>aucun</strong> remboursement ne sera possible.</p>
                                        
                                    </section>
                                </div>
                        </section>

                    <!-- Formulaire -->
						<section class="wrapper style4 container">

							<!-- Content -->
								<div class="content">
									<section>
										<header>
                                            <h3>Formulaire d'adhésion</h3>
										</header>

<?php err::print($erreurs) ?>

                                        <form action="" method="post">

                                            <div style="display:flex">
                                            
                                                <input style="width:40%" type="text" name="prenom" placeholder="Prénom" required/>
                                                <input style="width:40%; margin: 0 2%" type="text" name="nom" placeholder="Nom" required/>

                                                <select style="width:16%" name="classe" id="classe" required>
                                                
                                                    <option value="classe" disabled selected>Classe</option>
                                                    
<?php

    foreach (settings::p('gestion_adherents')['classes'] as $classe)
    {
        echo '<option value="'.$classe.'">'.$classe.'</option>';
    }

?>
                                                    
                                                </select>
                                                
                                            </div>

                                            <div style="display:flex; margin-top:18px;">

                                                <input style="width:82%; margin: 0 2% 0 0;" type="email" name="email" placeholder="Adresse mail" required/>
                                                
                                                <select style="width:16%" name="nl" id="nl" required>
                                                 
                                                    <option value="nl" disabled selected>Newsletter</option>

	 												<option value="non">NON</option>
                                                    <option value="oui">OUI</option>
                                                     
                                                </select>

                                            </div>

                                            <p style="font-style: italic; font-size: 87%; line-height: 100%; text-align:justify; margin-top:5px;">La Newsletter sert à recevoir,
                                            une fois par mois environ, toutes les informations que le BDE juge utiles de partager avec les étudiants de la prépa.
 											Si tu ne souhaites pas que ta boîte mail soit assaillie d'une newsletter supplémentaire, tu peux la refuser et simplement aller
                                            consulter le tableau d'affichage de l'association, à l'entrée du bâtiment 3.</p>
                                             
                                            <br>

                                            <p style="text-align:justify;font-size: 95%; line-height: 110%">
                                            Toutes les informations que tu nous communiques resteront <b>confidentielles</b> et seront <b>supprimées en fin d'année scolaire</b>.
                                            Nous te demandons une adresse mail afin de pouvoir vérifier ton profil lorsque tu pourras bénéficier de réductions sur certains événements.
                                            De plus, <b>l'identifiant</b> que tu recevras à l'issue de ton	inscription doit être <b>conservé précieusement</b> et <b>rester confidentiel</b>.
                                            Toute tentative de fraude te vaudra l'exclusion définitive de l'association.
                                            </p>

											<p style="text-align:center; margin-bottom:30px"><strong>En adhérant, tu acceptes la
                                            <a target="_blank" href="https://docs.bde-bp.fr/documents/charte.pdf">Charte</a> du BDE.</strong><br/>
                                            Tu peux également consulter les <a target="_blank" href="https://docs.bde-bp.fr/documents/statuts.pdf">Statuts</a> de l'association.</p>

                                            <div style="width:100%; display:flex; justify-content:center; margin-bottom: 40px;">
                                                <div class="g-recaptcha" data-sitekey="<?php echo $public_key; ?>"></div>
                                            </div>              

                                            <p style="text-align:center">
                                                <button type="submit" name="FormName" value="adhesion" class="button primary">Adhésion</button>
                                            </p>
                           
                                            <p style="text-align: left; opacity: 0.6; font-style: italic; padding: 2.5em 0 0 0; font-size: 16px">Tout les champs sont obligatoires</p>

										</form>
									</section>
                          
								</div>

<?php 
    } 
    else if (isset($confirmation) && $confirmation == true && $gestion_adherents->isRegistered())
    {
?>


                    <p>Ton inscription a été prise en compte ! Mais c'est pas fini...</p>

                </header>

                        <section class="wrapper style4 container">

                            <div style="padding-bottom:0em;" class="content">

                                <section>

                                    <h3 style="text-align:center">Ton numero d'adhérent :</h3>
<?php
if ($mobile)
{
    echo '<p style="font-size:125px;padding:75px 0px 0px 0px;text-align:center;margin:0px 0px 0px 0px;height:200px;">';
}
else
{
    echo '<p style="font-size:260px;padding:100px 0px 0px 0px;text-align:center;margin:0px 0px 0px 0px;height:275px;">';
}
echo $_SESSION['Adherent']->getIDA();
?>
</p>
                                    <p style="padding: 0px 0px 50px 0px;margin:0px 0px 0px 0px;height:0px;text-align:center;">A noter au dos du chèque ou sur HelloAsso</p>

                                </section>
                            </div>
                        </section>

                        <section class="wrapper style4 container">

                            <!-- Content -->

                                <div style="padding-bottom:0em;" class="content">

                                    <section>

                                        <h3>Tes infos :</h3>
                                        <ul>
                                            <li>Numéro d'adhérent : <?php echo $_SESSION['Adherent']->getIDA(); ?></li>
                                            <li>Status : <?php echo $_SESSION['Adherent']->getStatusString(); ?></li>
                                            <li>Prénom : <?php echo $_SESSION['Adherent']->getPrenom(); ?></li>
                                            <li>Nom : <?php echo $_SESSION['Adherent']->getNom(); ?></li>
                                            <li>Classe : <?php echo $_SESSION['Adherent']->getClasse(); ?></li>
                                            <li>Adresse mail : <?php echo $_SESSION['Adherent']->getEmail(); ?></li>
                                            <li>Newsletter : <?php echo $_SESSION['Adherent']->getNewsletterString(); ?></li>
                                        </ul>
                                        <p>Une des informations est inexacte ? <a href="?cancel=<?php echo $_SESSION['Adherent']->getIDA(); ?>">Recommencer l'inscription</a></p>

                                    </section>

                                </div>

                                <div class="content">

                                    <section>

                                        <h3>Instructions pour la suite : </h3>

                                        <p>Maintenant que tu as finalisé ton inscription, il ne te reste plus qu'à régler le montant de <strong>10€</strong> sous <strong>15 jours</strong> (le plus tôt sera le mieux). Tu peux le régler de deux façons:</p>
                                        <ul class="inner" style="padding:0em 0 2em 0; margin-bottom:0px;">
                                            <li style="padding-left: 2em"><strong>Par chèque</strong> : un chèque d'un montant de <strong>10€</strong> à l'ordre du 
                                            <ita>BDE de Blaise Pascal</ita> à déposer dans la boîte aux lettres de l'association, située dans le hall d'accueil, 
                                            en direction du bureau de la proviseur, 
                                            et si tu ne la trouves pas, demande à l'accueil de te montrer son emplacement. Renseigne <strong>obligatoirement</strong> 
                                            ton numéro d'adhérent suivie de la première lettre de ton prénom au dos du chèque, dans le coin inférieur droit. 
                                            Il est parfaitement possible de régler plusieures commandes en un seul chèque ; dans ce cas, répètes l'opération autant de fois que nécessaire.</li>
                                            <br>
                                            <li style="padding-left: 2em"><strong>Par carte bancaire</strong> : il suffit de remplir le formulaire HelloAsso ci-dessous. 
                                            HelloAsso est une plateforme gratuite qui permet aux associations de récolter des fonds sans frais (comme PayPal, 
                                            mais gratuit et réservé aux associations). L'organisme vis grâce aux pourboires, et <b>un pourboire sera automatiquement 
                                            sélectionné. Les pourboires ne nous sont pas reversés mais servent uniquement au fonctionnement de la plateforme HelloAsso !</b><br/>
                                            Nous tenons à préciser que toutes tes informations de paiement resteront confidentielles et ne nous serons jamais communiquées. 
                                            Certains champs (tels que l'adresse postale) nous sont imposés par HelloAsso et par la legislation française (pour lutter contre le blanchiment d'argent), 
                                            nous ne les utiliserons pas.<br/>
                                            En cas de problème, un lien direct vers la plateforme HelloAsso est disponible <a target="_blank" href="<?php echo $lienHelloAsso ?>">ICI</a></li>
                                        </ul>

<?php

    if ($mobile)
    {
        $style='width: auto; height: 750px; border: none;';
    }
    else
    {
        $style='width: 100%; height: 750px; border: none;';
    }

?>
                                        <iframe id='haWidget' src='<?php echo $lienHelloAsso ?>/widget' style="<?php echo $style ?>"></iframe>

                                    </section>

                                </div>

                        </section>

<?php 
    }
    else
    {
?>

</header>

<!-- SAD -->
    <section class="wrapper style4 container">
        <header>
            <h3>Pour l'instant c'est fermé</h3>
        </header>
        <div class="content">
            <p>Les adhésions au BDE sont pour l'instant fermées, voyez avec les représentants dans vos classes pour connaître la prochaine session d'inscription.</p>
        </div>
    </section>

<?php
    }
?>


				</article>



<?php include("../../dependances/construct/footer.php"); ?>



		</div>

		<!-- Scripts-->
			<script src="https://assets.bde-bp.fr/js/jquery.min.js"></script>
			<script src="https://assets.bde-bp.fr/js/jquery.dropotron.min.js"></script>
			<script src="https://assets.bde-bp.fr/js/jquery.scrolly.min.js"></script>
			<script src="https://assets.bde-bp.fr/js/jquery.scrollgress.min.js"></script>
			<script src="https://assets.bde-bp.fr/js/jquery.scrollex.min.js"></script>
			<script src="https://assets.bde-bp.fr/js/browser.min.js"></script>
			<script src="https://assets.bde-bp.fr/js/breakpoints.min.js"></script>
			<script src="https://assets.bde-bp.fr/js/util.js"></script>
			<script src="https://assets.bde-bp.fr/js/main.js"></script>

	</body>

</html>
