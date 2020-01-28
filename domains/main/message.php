<?php
include('../../dependances/class/base.php');



if (isset($_SESSION['MessagePage']))
{
	$msg = $_SESSION['MessagePage']['msg'];
	$error = $_SESSION['MessagePage']['error'];
	gestion_logs::Log($_SESSION['IP'], log::TYPE_VIEW, 'message', $msg);
	unset($_SESSION['MessagePage']);
}
else
{
    header('Location: https://bde-bp.fr');
    die();
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
		<title>BDE BP - <?php if ($error) { echo "Erreur"; } else { echo "Message"; } ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<script src="https://kit.fontawesome.com/1fdd8d0755.js" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="https://assets.bde-bp.fr/css/main.css" />
		<noscript><link rel="stylesheet" href="https://assets.bde-bp.fr/css/noscript.css" /></noscript>
		<link rel="shortcut icon" href="https://docs.bde-bp.fr/images/statiques/favicon.ico" />
	</head>
	<body class="contact is-preload">

	<?php include("../../dependances/construct/bandeau.php") ?> 
	
		<div id="page-wrapper">

			<!-- Header -->
			

<?php include("../../dependances/construct/header.php") ?>

			<!-- Main -->
				<article id="main">

					<header class="special container">
						<span class="icon fa-times-circle"></span>
						<?php

						if ($error)
						{
							echo "<h2><strong>ERREUR</strong></h2>";
						}
						else
						{
							echo "<h2><strong>MESSAGE</strong></h2>";
						}

						?>
					</header>



				<!-- One -->
					<section class="wrapper style4 container">

						<!-- Content -->
							<div class="content">
								<section>

									<header id="ici">
										<?php if ($msg != NULL) {echo "<p>$msg</p>";} ?>
									</header>
								</section>
							</div>
						</section>
				</article>

			<!-- Footer -->
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
