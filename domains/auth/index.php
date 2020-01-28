<?php

include('../../dependances/class/base.php');


if ($gestion_adherents->isConnected())
{
    header('Location: https://adherent.bde-bp.fr');
}

// vérification crédits mail
if ($gestion_mails->getCredits() >= 10)
{
    // last page
    if (isset($_SESSION['lastpage']))
    {
        $lastpage = (string) $_SESSION['lastpage'];
    }
    else
    {
        $lastpage = '';
    }

    // par defaut
    $show = 'login';

    // d'abord test des get: connectioncode, resetpass, forgotida, firstlogin (just affichage section)
    if (isset($_GET['connectioncode']) && isset($_GET['IDA']) && isset($_POST['code']) && $gestion_adherents->getAdherent($_GET['IDA']))
    {
        if (isset($_GET['lastpage']))
        {
            $query_lastpage = urldecode((string) $_GET['lastpage']);
        }

        if ($gestion_adherents->verifCode($_GET['IDA'], $_POST['code']))
        {
            $mail_auth = $gestion_adherents->verifKey((int) $_GET['IDA'], '', $_POST['code']);
            if (!($mail_auth === false) && $mail_auth['Password'] == NULL)
            {
                unset($_SESSION['login']['connectioncode']['ation']);
                
                $gestion_adherents->login($_GET['IDA'], '', false);
                if (isset($_GET['cookie']))
                {
                    $gestion_adherents->createConnectionCookie($_GET['IDA']);
                }
                gestion_logs::Log($_SESSION['IP'], log::TYPE_COMPTE, 'auth/login-logged', $_SESSION);

                if ($query_lastpage != '')
                {
                    header('Location: '.$query_lastpage);
                    unset($_SESSION['lastpage']);
                }
                else
                {
                    header('Location: https://adherent.bde-bp.fr');
                }
            }
            else
            {
                $erreurs[] = "Le code est obsolète, veuillez vous reconnecter.";
                unset($_SESSION['login']['connectioncode']['ation']);
            }
        }
        else
        {
            $erreurs[] = "Le code n'est pas correct.";
            $show = 'connectioncode';
        }
    }
    else if (isset($_GET['resetpass']))
    {
        if (isset($_POST['IDA']))
        {
            if ($gestion_adherents->isAdherent($_POST['IDA']))
            {
                if ($gestion_adherents->GotAPass($_POST['IDA']))
                {
                    $genKey = $gestion_adherents->genKey((int) $_POST['IDA']);

                    $link = "https://auth.bde-bp.fr/mail_query?resetpass&key=".$genKey['key']."&IDA=".$_POST['IDA'];
                    $adherent = $gestion_adherents->getAdherent($_POST['IDA']);
                    $gestion_mails->mail_ResetPass($link, $adherent);

                    MessagePage("Nous vous avons envoyé un mail (à ".$adherent->getEmailCensored().") pour réinitialiser votre mot de passe.");
                    die();
                }
                else
                {
                    $show = 'resetpass';
                    $erreur[] = "Cet adhérent ne possède pas de mot de passe.<br>
                    Si vous voulez vous connectez, passez par l'option Première connexion (sur la page de connexion).";
                }
                    
            }
            else
            {
                $show = 'resetpass';
                $erreur[] = "Cet adhérent n'existe pas";
            }
        }
        else
        {
            $show = 'resetpass';
        }
    }
    else if (isset($_GET['forgotida']))
    {
        if (isset($_POST['email']))
        {
            $adherent = $gestion_adherents->getAdherentFromMail($_POST['email']);
            if ($adherent)
            {
                $gestion_mails->mail_GetIDA($adherent);

                MessagePage("Nous vous avons envoyé un mail qui contient votre identifiant.");
                die();
                    
            }
            else
            {
                $show = 'forgotida';
                $erreur[] = "Nous n'avons pas trouvé d'adhérent avec cette adresse mail.";
            }
        }
        else
        {
            $show = 'forgotida';
        }
    }
    else if (isset($_GET['firstlogin']))
    {
        $show = 'firstlogin';
    }




    // enfin gestion des post : login, firstlogin,
    if (isset($_POST['FormName']))
    {
        
        // si créeation cookie de connection 
        if (isset($_POST['cookie']))
        {
            $cookie = '&cookie';
        }
        else
        {
            $cookie = '';
        }
        
        
        if ($_POST['FormName'] == 'login')
        {
            // Log
            $logPost = $_POST;
            unset($logPost['pass']);
            gestion_logs::Log($_SESSION['IP'], log::TYPE_COMPTE, 'auth/login-login-ask', $logPost);

            // Connexion 
            $login = $gestion_adherents->loginHandler($_POST);
            if (is_a($login, 'adherent'))
            {
                
                if ($gestion_adherents->authRole($login, adherent::ROLE_CA))
                {
                    gestion_logs::Log($_SESSION['IP'], log::TYPE_COMPTE, 'auth/login-login-mail_auth', '');

                    $genKey = $gestion_adherents->genKey((int) $_POST['IDA'], true);

                    // Pour le formulaire du code
                    $_SESSION['login']['connectioncode']['ation'] = "?connectioncode&IDA=".$_POST['IDA']."&lastpage=".urlencode($lastpage).$cookie;

                    // envoi du mail
                    $link = "https://auth.bde-bp.fr/mail_query?key=".$genKey['key']."&IDA=".$_POST['IDA']."&lastpage=".urlencode($lastpage).$cookie;
                    $adherent = $gestion_adherents->getAdherent($_POST['IDA']);
                    $gestion_mails->mail_AuthKey($link, $genKey['code'], $adherent);

                    // ici erreurs permet de faire passer un message de confirmation
                    $erreurs[] = "<p style=\"color: rgb(51, 204, 51); font-weight: bold;\">Nous vous avons envoyé un mail (à ".$adherent->getEmailCensored().") pour vous connecter.<br>
                    Vous pouvez cliquer sur le lien qui vous a été envoyé pour vous connecter sur l'appareil ayant reçu le mail,
                    ou entrer le code reçu pour vous connecter sur cet appareil.<br>
                    <br>
                    Les codes et liens ne seront valide que pendant 5 minutes.</p>";

                    $show = 'connectioncode';
                }
                else
                {
                    gestion_logs::Log($_SESSION['IP'], log::TYPE_COMPTE, 'auth/login-login-logged', $_SESSION);

                    $gestion_adherents->login($_POST['IDA'], '', false);
                    $gestion_adherents->createConnectionCookie($_POST['IDA']);

                    if ($lastpage != '')
                    {
                        header('Location: '.$lastpage);
                        unset($_SESSION['lastpage']);
                    }
                    else
                    {
                        header('Location: https://adherent.bde-bp.fr');
                    }
                }
                

            }
            else
            {
                $erreurs = $login;
                $show = 'login';
            }
        }
        elseif ($_POST['FormName'] == 'firstlogin')
        {
            $logPost = $_POST;
            unset($logPost['pass1']);
            unset($logPost['pass2']);
            gestion_logs::Log($_SESSION['IP'], log::TYPE_COMPTE, 'auth/login-firstlogin-ask', $logPost);

            $firstlogin = $gestion_adherents->firstloginHandler($_POST);
            if ($firstlogin === true)
            {
                gestion_logs::Log($_SESSION['IP'], log::TYPE_COMPTE, 'auth/login-firstlogin-mail_auth', '');

                $genKey = $gestion_adherents->genKey((int) $_POST['IDA'], false, 64, password_hash($_POST['pass1'], PASSWORD_DEFAULT));
                $link = "https://auth.bde-bp.fr/mail_query?confirmpass&key=".$genKey['key']."&IDA=".$_POST['IDA']."&lastpage=".urlencode($lastpage).$cookie;
                $adherent = $gestion_adherents->getAdherent($_POST['IDA']);
                $gestion_mails->mail_ConfirmPass($link, $adherent);
                

                $erreurs[] = "<p style=\"color: rgb(51, 204, 51); font-weight: bold;\">Nous vous avons envoyé un mail (à ".$adherent->getEmailCensored().") pour confirmer cette action.<br>
                Le lien ne sera valide que pendant 15 minutes.</p>";
            }
            else
            {
                $erreurs = $firstlogin;
                $show = 'firstlogin';
            }
        }
    }
}
else
{
    $show = 'outofmail';
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
		<title>BDE - Authentification</title>
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
        <script src="https://kit.fontawesome.com/1fdd8d0755.js" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="https://assets.bde-bp.fr/css/main.css" />
		<noscript><link rel="stylesheet" href="https://assets.bde-bp.fr/css/noscript.css" /></noscript>
		<link rel="shortcut icon" href="https://docs.bde-bp.fr/images/statiques/favicon.ico" />

    </head>

	<body class="no-sidebar is-preload">

<?php include("../../dependances/construct/bandeau.php") ?> 

		<div id="page-wrapper">

<?php include("../../dependances/construct/header.php") ?>



			<!-- Main -->
				<article id="main">
					<header class="special container">
						<span class="icon fa-cogs"></span>
						<h2><strong>Authentification</strong></h2>
						<p>Acceder à votre Espace personnel.</p>
					</header>

<?php

    if ($show == 'connectioncode' && isset($_SESSION['login']['connectioncode']['ation']))
    {

?>
                    <!-- Code connexion -->
                        <section class="wrapper style4 container">

                        <!-- Content -->
                            <div class="content">
                                <section>
                                    <header>
                                        <h3>Code de vérification</h3>
                                    </header>

                                    <?php err::print($erreurs) ?>

                                    <form action="<?php echo $_SESSION['login']['connectioncode']['ation']; ?>" method="post" id="code">
                                        <div style="display:flex;margin-bottom:20px">
                                            <input form="code" style="width:49%; margin-right:2%;" type="number" name="code" placeholder="Code" required/>
                                            <button type="submit" style="width: 49%" form="code" class="button primary">Valider</button>
                                        </div>
                                    </form>
            

                                </section>
                            </div>

                        </section>

<?php

    }
    else if ($show == 'resetpass')
    {
        
?>

                    <!-- Réinitialisation du mdp -->
                        <section class="wrapper style4 container">

                        <!-- Content -->
                            <div class="content">
                                <section>
                                    <header>
                                        <h3>Réinitialisation du mot de passe</h3>
                                    </header>

                                    <?php err::print($erreurs) ?>

                                    <form action="?resetpass" method="post" id="reset">
                                        <div style="display:flex;margin-bottom:20px">
                                            <input form="reset" style="width:49%; margin-right:2%;" type="number" name="IDA" placeholder="Identifiant d'adhérent" required/>
                                            <button type="submit" style="width: 49%" form="reset" class="button primary">Valider</button>
                                        </div>
                                    </form>

                                    <a style="" href="?" class="button">Annuler</a>


                                </section>
                            </div>

                        </section>

<?php

    }
    else if ($show == 'forgotida')
    {
    
?>

                <!-- Récuperer son IDA -->
                    <section class="wrapper style4 container">

                    <!-- Content -->
                        <div class="content">
                            <section>
                                <header>
                                    <h3>Récupérer mon identifiant (numéro) d'adhérent</h3>
                                </header>

                                <?php err::print($erreurs) ?>

                                <form action="?forgotida" method="post" id="forgotida">
                                    <div style="display:flex;margin-bottom:20px">
                                        <input form="forgotida" style="width:49%; margin-right:2%;" type="email" name="email" placeholder="Adresse mail" required/>
                                        <button type="submit" style="width: 49%" form="forgotida" class="button primary">Valider</button>
                                    </div>
                                </form>

                                <a style="" href="?" class="button">Annuler</a>


                            </section>
                        </div>

                    </section>


<?php

    }
    else if ($show == 'firstlogin')
    {


?>
                
                <!-- Premiere connexion -->
						<section class="wrapper style4 container">

							<!-- Content -->
								<div class="content">
									<section>
										<header>
                                            <h3>Première connexion - créer un mot de passe</h3>
										</header>

                                        <?php err::print($erreurs) ?>

										<form action="" method="post" id="firstlogin">
                                            <div style="display:flex; margin: 0 0 2% 0">
                                                <input style="width:49%" type="text" name="IDA" placeholder="Numéro d'adherent" required/>
                                                <input style="width:49%; margin: 0 0 0 2%" type="text" name="email" placeholder="Adresse Email" required/>
                                            </div>
                                            <div style="display:flex; margin: 0 0 2% 0">
                                                <input style="width:49%" type="password" name="pass1" placeholder="Mot de passe" required/>
                                                <input style="width:49%; margin: 0 0 0 2%" type="password" name="pass2" placeholder="Vérification du mot de passe" required/>
                                            </div>
                                            <!-- <div class="container_checkbox">
                                                <input form="login" classe="check-box" type="checkbox" name="cookie" id="cookie" checked><label for="cookie">Rester connecté</label>
                                            </div> -->
                                            <p style="text-align:right">
                                                <a style="margin-right:2%" href="?" class="button">Retour</a>
                                                <button style="width:30%" type="submit" form="firstlogin" name="FormName" value="firstlogin" class="button primary">Créer un mot de passe</button>
                                            </p>
										</form>
									</section>
                                </div>
                        </section>

<?php

    }
    else if ($show == 'login')
    {

?>


					<!-- Connexion -->
						<section class="wrapper style4 container">

							<!-- Content -->
								<div class="content">
									<section>

                                        <?php err::print($erreurs) ?>


										<form action="" method="post" id="login">
                                            <div style="display:flex;margin-bottom:20px">
												<input form="login" style="width:49%; margin-right:2%;" type="text" name="IDA" placeholder="Numéro d'adherent" required/>
												<input form="login" style="width:49%" type="password" name="pass" placeholder="Mot de passe" required/>
                                            </div>
                                            <div class="container_checkbox">
                                                <input form="login" classe="check-box" type="checkbox" name="cookie" id="cookie" checked><label for="cookie">Rester connecté</label>
                                            </div>
                                            <p style="font-style: italic; font-size: 87%; line-height: 100%; text-align:right; margin-top:0px;">
                                                Par défaut il n'y a pas de mot de passe lié à votre compte. Pour en créer un cliquez sur le bouton "Créer un mot de passe".
                                            </p>
                                            <p style="text-align:right">
                                                <a style="margin-right:2%" href="?firstlogin" class="button">Créer un mot de passe</a>
                                                <button type="submit" form="login" name="FormName" value="login" class="button primary">connexion</button>
                                            </p>
                                            <p><a href="?resetpass">Mot de passe oublié</a> - <a href="?forgotida">Identifiant d'adhérent oublié</a></p>
                                        </form>
                                        

									</section>
								</div>



						</section>




<?php

    }
    else if ($show == 'outofmail')
    {
?>
        <!-- Plus de mail dispo -->
            <section class="wrapper style4 container">

            <!-- Content -->
                <div class="content">
                    <section>
                        <header>
                            <h3>Aïe</h3>
                        </header>

                        Nous ne pouvons plus envoyer de mail pour aujourd'hui, de ce fait et car la connexion est sécurisée par l'envoi de mails,
                        vous ne pouvez plus vous connecter jusqu'à demain 00h00.


                    </section>
                </div>

            </section>
<?php
    }
    else
    {
        echo "Uhhmmmm il devrait y avoir quelque chose ici, mais rien n'est apparu... Essayez de revenir sur cette page depuis l'accueil du site.";
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
