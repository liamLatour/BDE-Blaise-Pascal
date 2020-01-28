<?php

include('../../dependances/class/base.php');

?>


<!DOCTYPE HTML>
<!--
	Twenty by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>

<head>
		<title>Bureau des Etudiants - CPGE Blaise Pascal, Clermont-Ferrand</title>
		<meta name="description" content="Vous trouverez sur ce site toutes les informations concernant le BDE de Blaise Pascal : fonctionnement, articles, projets, annonces... Rejoignez-nous vite !" >
		<meta name="keywords" content="bde, bureau des élèves, blaise, pascal, lycée, clermont, ferrand, auvergne, puy de dome, puy-de-dôme, cpge, prépa, classe, étudiant, élève, etudiant, eleve, prepa, lycee, bureau, bureau des eleves, carnot, avenue, ent, uca, superieur, enseignement, bal, sport, cours, rentrée, rentree, vacances, université, fac, universite, projet, article, annonce, integration, conseil, administration, intégration, concours, grandes, ecoles, écoles">
		<meta name="google-site-verification" content="Y5V0MphSu_igBNj57B6KZ_3prjTnOatH6ZZ4fQNadxM" />
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<script src="https://kit.fontawesome.com/1fdd8d0755.js" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="https://assets.bde-bp.fr/css/main.css" />
		<noscript>
            <link rel="stylesheet" href="https://assets.bde-bp.fr/css/noscript.css" />
        </noscript>
		<link rel="shortcut icon" href="https://docs.bde-bp.fr/images/statiques/favicon.ico" />
    </head>
    

	<body class="index is-preload">

<?php include("../../dependances/construct/bandeau.php") ?> 

		<div id="page-wrapper">   

			<!-- Header spécial accueil-->
				<header id="header" class="alt">
					<aside style=""><a href="index" style="border-bottom: solid 0px;"><img src="https://docs.bde-bp.fr/images/statiques/logo.png" alt=""></a></aside>
					<div>
                        <h1 id="logo">
                            <a href="index">BDE<span> Blaise Pascal</span></a>
                        </h1>
                    </div>
                    <nav id="nav">
                        <ul>
					
<?php include("../../dependances/construct/nav.php") ?>

				        </ul>
                    </nav>	  
                </header>



			<!-- Bannière -->
				<section id="banner">

					<!--
						".inner" is set up as an inline-block so it automatically expands
						in both directions to fit whatever's inside it. This means it won't
						automatically wrap lines, so be sure to use line breaks where
						appropriate (<br />).
                    -->
                    
					<div class="inner">
						<header>
							<h2>Bureau <br />des étudiants</h2>
						</header>
						<p>Le site web du <strong>BDE</strong> du lycée <strong>Blaise Pascal</strong> </br>Clermont-Ferrand</p>
						<footer>
							<ul class="buttons stacked">
								<li><a href="#main" class="button fit scrolly">En avant les licornes</a></li>
							</ul>
						</footer>

					</div>

				</section>


			<!-- Main -->
				<article id="main">

                    <section class="container" style="text-align: center;">
                            <iframe id="haWidget" allowtransparency="true" src="https://www.helloasso.com/associations/bde-de-blaise-pascal/evenements/roses-saint-valentin-2020-1/widget-vignette" style="width:350px;height:450px;border:none;"></iframe>
                            <iframe id="haWidget" allowtransparency="true" src="https://www.helloasso.com/associations/bde-de-blaise-pascal/evenements/sortie-ski-2020-1/widget-vignette" style="width:350px;height:450px;border:none;"></iframe>
                    </section>

					<header class="special container">
						<span style="font-size: 3em; position: relative;top: -100px;">
							<i class="fas fa-question"></i>
						</span>
						<h2>Mais dis-moi <strong>Jamy</strong>, C'est quoi un <strong>BDE</strong> ?<br />
						Eh bien <strong>Fred</strong>, c'est très simple :</h2>
						<p

<?php
// Detection client mobile
if ($mobile)
{
    echo "style='text-align:justify'";
}
?>
						>
						Le bureau des élèves est l'interlocuteur privilégié des étudiants et l'un des piliers de la vie étudiante.
						Il représente les élèves auprès de l'administration et d'interlocuteurs extérieurs.
						Et bien sûr, il organise toutes sortes d'événements pour animer la vie du lycée et la rendre la plus conviviale possible : événements culturels, tournois sportifs, projets humanitaires ou autres.</p>
						<p
<?php
// Detection client mobile
if ($mobile)
{
    echo "style='text-align:justify'";
}
?>
                        >
                        Mais avant tout, le BDE c'est une <strong>grande famille</strong> d'étudiants qui se serrent les coudes face aux difficultés de la prépa ! </p>
					</header>

					<!-- Adhesion (à rendre dynamique) -->
						<section class="wrapper style2 container special-alt">
							<div class="row gtr-50">
								<div style="text-align:justify">

									<header>
										<h2 style="text-align:center">Pourquoi adhérer au <strong>BDE ?</strong></h2>
									</header>
									<p>Adhérer au BDE c’est participer au dynamisme de la vie étudiante à Blaise Pascal au travers de nombreux projets et participer à l'esprit de cohésion et d'entraide entre classes et filières que l'association crée grâce à vous !</p>
									<p>Votre statut d’adhérent vous donne droit à de nombreux avantages : réductions sur les photos de classe et les pulls, accès gratuit ou réduction sur
											de nombreuses activités organisées au cours de l’année, possibilité de représenter votre classe au Conseil d’Administration de l’association
										 	et de participer plus ou moins activement à la vie de celle-ci. Pour les plus motivés : siège au bureau, proposition et organisation
										 	d’événements …!</p>
									<p>Etant géré intégralement par les étudiants, le BDE a besoin de vos adhésions pour vivre et pouvoir organiser un maximum d’activités.
											A l'heure actuelle, les seules ressources de l'association sont vos cotisations... C'est pour cela que nous avons besoin de vous !</p>
									<p>Bref, le BDE c’est une petite participation de 10€ en début d’année pour que l’association puisse vivre et proposer
											un maximum de projets divers et variés, le tout dans le but de rendre vos années de classe préparatoire les plus agréables possibles.<p>
									<footer>
										<ul class="buttons" style="text-align:center">
                                            <?php
                                            if (gestion_adherents::publicationAdhesion())
                                            {
                                                echo '<li><a href="https://adherent.bde-bp.fr/adhesion" class="button">Adhérer</a></li>';
                                            }
                                            else
                                            {
                                                echo '<li><a class="button">Les adhésions sont désactivées pour l\'instant</a></li>';
                                            }
                                            ?>
										</ul>
									</footer>

								</div>

							</div>
						</section>

					<!-- Explication BDE -->
						<section class="wrapper style1 container special">
							<div class="row">
								<div class="col-4 col-12-narrower">

									<section>
										<i class="fas fa-check" style="font-size: 3em; margin-bottom: 10px;"></i>
										<header>
											<h3>Le BDE c'est :</h3>
										</header>
										<p>Une association par et pour les étudiants, qui a pour but de dynamiser la vie étudiante au lycée et de favoriser l’entente inter-classes et inter-filières.</p>
									</section>

								</div>
								<div class="col-4 col-12-narrower">

									<section>
										<i class="fas fa-check" style="font-size: 3em; margin-bottom: 10px;"></i>
										<header>
											<h3>Mais aussi :</h3>
										</header>
										<p>Des compétitions sportives, des événements culturels, des soirées, des sorties, et même toute activité que vous pouvez nous proposer ! (Toutes les idées sont les bienvenues)</p>
									</section>

								</div>
								<div class="col-4 col-12-narrower">

									<section>
										<i class="fas fa-check" style="font-size: 3em; margin-bottom: 10px;"></i>
										<header>
											<h3>Et enfin :</h3>
										</header>
										<p>Des réductions ou la gratuité sur vos photos de classe et vos pulls, sur les sorties organisées, les bals... Tous nos efforts pour vous proposer le meilleur du meilleur !</p>
									</section>

								</div>
							</div>
						</section>

                    <!-- Articles (à rendre dynamique) -->
                    
                </article>

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
