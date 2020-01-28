<?php
include('../../dependances/class/base.php');
$gestion_salles = new gestion_salles($bdd);
$gestion_salles->loadContributeur();

$IDC = $gestion_salles->getIDC();
if ($IDC === false)
{
    MessagePage("Une erreur est survenue, nous n'avons pas pu récuperer vos informations de connexion. Veuillez réessayer <a href=\"https://salles.bde-bp.fr\">ici</a>.");
    die();
}

$lvl = $gestion_salles->getLVL($IDC);
$lvl_floor = floor($lvl);
$lvl_bar = ($lvl - $lvl_floor)*100;

$joke = $gestion_salles->getJoke();

$jour = gestion_salles::getCurrentJour();
$h_start = gestion_salles::getCurrentHeure();
$h_stop = $h_start + 1;
if (!gestion_salles::verifHeure($h_start) || !$jour)
{
    $disabled = true;
}


?>
<!DOCTYPE html>
<html>
    <head>
        <title>BDE - LASALLE</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="utf-8" />
        <link rel="stylesheet" href="./css/bootstrap.min.css">
        <link rel="stylesheet" href="./css/style.css">
        <link rel="shortcut icon" href="https://docs.bde-bp.fr/images/statiques/favicon.ico" />
    </head>
    <body>
        <div class="container-fluid p-0" style="max-width: 600px; min-width: 200px;"">
            <div class="container p-3">

                <div class="mb-5">
                    <!-- Logo -->
                    <div class="row mb-2">
                        <div class="col-2"></div>
                        <div class="col-8">
                            <svg id="Calque_1" data-name="Calque 1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 500 500"><defs><style>.cls-1{fill:url(#Dégradé_sans_nom_22);}.cls-2{fill:#fff;}</style><linearGradient id="Dégradé_sans_nom_22" x1="3.76" y1="293.42" x2="496.24" y2="206.58" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#e41c39"/><stop offset="0.25" stop-color="#e43534"/><stop offset="1" stop-color="#e4b31c"/></linearGradient></defs><title>LaSalle-Logo-Rond</title><circle class="cls-1" cx="250" cy="250" r="250"/><path class="cls-2" d="M29.49,331.29l-3-84.29,2.95-.52,2.8,81.33,41.12-7.25.18,3Z"/><path class="cls-2" d="M143,311.28l-3.14.55-11.89-28.2-38,6.7L80.6,322.27l-3.13.56,27.2-89.62,5.23-.92Zm-16-30.47-19.09-45.87-.66.11L91,287.15Z"/><path class="cls-2" d="M159.2,281.59c4.52,4.59,11.84,6,18,5,5.58-1,10.1-3.82,10.16-8.26,0-5.28-7.56-6.21-14.35-6-13.26.2-26.7-8.09-26.53-24.12.16-17.44,14-26,29.05-28.65,10-1.76,19.79-.62,28.69,8.48L191,241.12c-4.18-3.32-10-4.23-14.75-3.49-5.25.81-9.33,3.56-9.05,7.83.26,4.86,4.82,6.26,11.3,6.51,14.76.15,29.67,3.15,29.43,24.25-.19,16.57-12.69,27-30.46,30.17-9.85,1.74-22.95-1.1-31-9.87Z"/><path class="cls-2" d="M286.12,286,263.82,290,257.77,274l-22,3.87-4.41,17.78-22.3,3.94,25.72-89.36,18.94-3.34Zm-33.94-30.1-6.67-23.61-.82.15-4.82,25.63Z"/><path class="cls-2" d="M288.46,200.8l20.91-3.68,2.17,63.22,33.48-5.9.82,21.07-54.36,9.58Z"/><path class="cls-2" d="M350.84,189.8l20.92-3.68,2.17,63.23,33.48-5.9.84,21.05-54.4,9.59Z"/><path class="cls-2" d="M435.34,207.73l33.35-5.88.69,18.93-33.33,5.88.43,12.63,36.24-6.39.79,20.09-57.28,10.1-3-84.29,57.28-10.1.68,20.19-36.25,6.39Z"/></svg>
                        </div>
                    </div>

                    <!-- Blague -->
                    <span class="text-white" style="display: flex; justify-content: center;"><small><?php echo $joke; ?></small></span>
                    <div class="container mb-1 mt-4" style="border-top-style: solid; border-top-width: 1px; border-top-color: white;">
                    </div>
                    
                    
                    <!-- Message -->
                    <div>
                        <span style="display: flex; justify-content: center;"><b>INFORMATION</b></span>
                        <div class="container mt-2 mb-1" style="border-top-style: solid; border-top-width: 1px; border-top-color: white;">
                        </div>
                    <!-- <div class="alert alert-info p-1 my-2">
                        <p class="m-0" style="line-height: 15px; text-align: center"><small>LaSalle est un service communautaire. Afin de pouvoir en profiter pleinement et de ne pas déteriorer inutilement l'expérience des autres utilisateurs, merci de ne pas partager volontairement des informations erronées.</small>
                        </p> -->
                        <p class="mt-2" style="line-height: 15px; text-align: center"><small>LaSalle est un service communautaire. Afin de pouvoir en profiter pleinement et de ne pas déteriorer inutilement l'expérience des autres utilisateurs, merci de ne pas partager volontairement des informations erronées.</small></p>
                    </div>

                    <div class="alert alert-warning">
                        Nous venons d'ajouter la fonctionnalité de mise à jour du remplissage d'une salle,
                        il se peut qu'elle soit instable et peu précise pour l'instant.
                    </div>

                    <!-- Niveau -->

                    <!-- Si Partenaire, la jauge de niveau change de couleur
                    Le CSS qui va bien dans la classe ".niveau" :
                    linear-gradient(90deg, rgba(23,162,184,1) 0%, rgba(23,162,184,1) 25%, rgba(228,28,183,1) 100%); -->

                    <div class="mt-5">
                        <div style="margin-bottom: -10px;">
                            <span><b>NIVEAU <?php echo $lvl_floor; ?></b></span>
                        </div>
                        <div class="progress" style="margin-top: 8px;">
                            <div class="progress-bar niveau" role="progressbar" style="width: <?php echo $lvl_bar; ?>%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div style="line-height: 10px;">
                            <small class="text-muted">Pour conserver votre progression vous devez vous connecter au service au moins une fois par mois.</small>
                        </div>
                    </div>

                    <!-- Bouton Recherche -->
                    <div class="p-5">
                        <a class="btn btn-block btn-primary py-1 px-3 py-3 font-weight-bold <?php if (isset($disabled)) { echo 'disabled'; }?>" href="search">
                            <?php
                                if (!isset($disabled))
                                {
                            ?>
                                    <span style="line-height: 15px">Rechercher une Salle</span>
                                    <div style="margin-top: -10px">
                                        <small><?php echo $h_start; ?>h - <?php echo $h_stop; ?>h</small>
                                    </div>
                            <?php
                                }
                                else
                                {
                            ?>
                                    <span style="line-height: 15px">Le service n'est pas disponible actuellement</span>
                            <?php
                                }
                            ?>

                            
                        </a>
                    </div>
                </div>


                <!--Partenaire-->
                <div class="container-fluid fixed-bottom">
                    <!-- Bêta -->
                    <div class="pb-1">
                        <span class="badge badge-secondary">BETA 1.1</span><span class="text-secondary"><small> LASALLE - Sortie prévue en fin de semaine</small></span>
                    </div>
                    <!-- <center>
                    <div class="container pb-4" style="border-top-style: solid; border-top-width: 1px; border-color: white; display: flex; justify-content: center; background: #100e17;">
                        <a>
                            <svg class="bi bi-chevron-compact-left" width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M11.224 3.553a.5.5 0 01.223.67L8.56 10l2.888 5.776a.5.5 0 11-.894.448l-3-6a.5.5 0 010-.448l3-6a.5.5 0 01.67-.223z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-center"><b>PARTENAIRE</b></span>
                            <svg class="bi bi-chevron-compact-right" width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M8.776 3.553a.5.5 0 01.671.223l3 6a.5.5 0 010 .448l-3 6a.5.5 0 11-.894-.448L11.44 10 8.553 4.224a.5.5 0 01.223-.671z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    </div>
                    </center> -->
            </div>
        </div>
    </body>
</html>