<?php
include('../../dependances/class/base.php');
$gestion_salles = new gestion_salles($bdd);


$url = "https://www.google.com/recaptcha/api/siteverify";
$public_key = "6LeBF3sUAAAAABFHrrRKv4_OMy4jzjZHO7kWIOYA";
$private_key = "6LeBF3sUAAAAALptB0OxezByVAYQ6rBautLqcr0m";


if (isset($_GET['next']) && isset($_SESSION['salles']['tuto_step']) && $_SESSION['salles']['tuto_step'] < 8)
{
    $_SESSION['salles']['tuto_step']++;
}

if (
    isset($_POST['go'])
    && isset($_SESSION['salles']['tuto_step'])
    && $_SESSION['salles']['tuto_step'] >= 8
    && isset($_POST['g-recaptcha-response'])
    && isset($_POST['pass'])
    && (strtoupper($_POST['pass']) == 'CARNOT'))
{
    $response_key = $_POST['g-recaptcha-response'];
    $response = file_get_contents($url.'?secret='.$private_key.'&response='.$response_key);
    $response = json_decode($response);

    if ($response->success == 1)
    {
        $gestion_salles->finishTuto();
    }
    
}

$gestion_salles->loadContributeur();


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
        <script src='https://www.google.com/recaptcha/api.js'></script>
    </head>
    <body>
        <div class="container-fluid p-0" style="max-width: 600px; min-width: 200px;">
            <div class="container pt-2 pr-4 pl-4">
                <!-- Logo -->
                <div class="row mb-2" style="max-width: 400px; min-width: 200px;">
                    <div class="col-2"></div>
                    <div class="col-8">
                        <svg id="Calque_1" data-name="Calque 1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 500 500"><defs><style>.cls-1{fill:url(#Dégradé_sans_nom_22);}.cls-2{fill:#fff;}</style><linearGradient id="Dégradé_sans_nom_22" x1="3.76" y1="293.42" x2="496.24" y2="206.58" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#e41c39"/><stop offset="0.25" stop-color="#e43534"/><stop offset="1" stop-color="#e4b31c"/></linearGradient></defs><title>LaSalle-Logo-Rond</title><circle class="cls-1" cx="250" cy="250" r="250"/><path class="cls-2" d="M29.49,331.29l-3-84.29,2.95-.52,2.8,81.33,41.12-7.25.18,3Z"/><path class="cls-2" d="M143,311.28l-3.14.55-11.89-28.2-38,6.7L80.6,322.27l-3.13.56,27.2-89.62,5.23-.92Zm-16-30.47-19.09-45.87-.66.11L91,287.15Z"/><path class="cls-2" d="M159.2,281.59c4.52,4.59,11.84,6,18,5,5.58-1,10.1-3.82,10.16-8.26,0-5.28-7.56-6.21-14.35-6-13.26.2-26.7-8.09-26.53-24.12.16-17.44,14-26,29.05-28.65,10-1.76,19.79-.62,28.69,8.48L191,241.12c-4.18-3.32-10-4.23-14.75-3.49-5.25.81-9.33,3.56-9.05,7.83.26,4.86,4.82,6.26,11.3,6.51,14.76.15,29.67,3.15,29.43,24.25-.19,16.57-12.69,27-30.46,30.17-9.85,1.74-22.95-1.1-31-9.87Z"/><path class="cls-2" d="M286.12,286,263.82,290,257.77,274l-22,3.87-4.41,17.78-22.3,3.94,25.72-89.36,18.94-3.34Zm-33.94-30.1-6.67-23.61-.82.15-4.82,25.63Z"/><path class="cls-2" d="M288.46,200.8l20.91-3.68,2.17,63.22,33.48-5.9.82,21.07-54.36,9.58Z"/><path class="cls-2" d="M350.84,189.8l20.92-3.68,2.17,63.23,33.48-5.9.84,21.05-54.4,9.59Z"/><path class="cls-2" d="M435.34,207.73l33.35-5.88.69,18.93-33.33,5.88.43,12.63,36.24-6.39.79,20.09-57.28,10.1-3-84.29,57.28-10.1.68,20.19-36.25,6.39Z"/></svg>
                    </div>
                    <div class="col-2"></div>
                </div>
            </div>

<?php
if (isset($_SESSION['salles']['tuto_step']) && $_SESSION['salles']['tuto_step'] == 1)
{
?>

            <!-- ETAPE 1 -->
            <div class="my-5 pb-5 px-3">
                <div style="padding-bottom: 5px; border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: white;">
                    <span><b>BIENVENUE SUR LASALLE</b></span>
                </div>
                <div class="mt-2">
                    <span>LaSalle est un service communautaire qui a pour objectif de faciliter la vie des étudiants en CPGE en proposant chaque heure les salles disponibles pour y travailler.
                    </br>Le service dépend grandement de ses utilisateurs. C'est avec du temps et des collaborateurs que la recherche deviendra précise.
                    </span>
                </div>
                <div class="mt-5" style="display: flex; align-items: center; justify-content: center;">
                    <a href="?next" class="btn btn-primary" style="width: 200px;">Commencer le tutoriel</a>
                </div>

            </div>

<?php
}
else if (isset($_SESSION['salles']['tuto_step']) && $_SESSION['salles']['tuto_step'] == 2)
{
?>
            <!-- ETAPE 2 -->
            <div class="mt-0 px-3">
                <div style="padding-bottom: 5px; border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: white;">
                    <span><b>LES HORAIRES</b></span>
                </div>
                <div class="mt-2">
                    <p>La recherche s'effectue par créneaux d'une heure. Le créneau est choisi selon l'heure actuelle, mais il peut être changé en haut de la page.</br>
                    Attention, passé 50 la recherche s'effectue pour la plage horaire suivante.</p>
                </div>


                <!-- Exemple Salle -->
                <div class="container-fluid salle mt-4">
                    <div class="row">

                        <!--Numéro & Horaires-->
                        <div class="col-6 border-right border-white" style="height: 82px;">
                            <div class="text-center" style="margin-top: -17px;">
                                <p style="line-height: 15px;">
                                    <span class="text-primary font-weight-bold m-0 display-4" style="opacity: 0.2">301</span></br>
                                        <span class="text-secondary display-5 m-0 p-0">12h - 13h</span></br>
                                        <small class="text-secondary m-0 p-0" style="opacity: 0.2">Pleine</small></br>
                                </p>
                                    </div>
                        </div>

                        <!--J'y Vais-->
                        <div class="col-6 border-left border-white" style="height: 82px;">
                            <div style="padding-top: 22px;">
                                <div class="pb-2" style="display: flex; justify-content: right; opacity: 0.2">
                                    <button class="btn btn-success btn-block align-self-center">J'y vais</button>
                                </div>
                            </div>
                        </div>

                        <!--Trust Bar-->
                        <div class="container-fluid mt-3" style="opacity: 0.2">
                            <div class="row">
                                <div class="col-1 pr-0" style="margin-top: -8px; margin-left: -10px; margin-right: 10px">
                                    <a style="display: inline-block; height: 32px; width: 32px;">
                                        <svg class="bi bi-dash" width="32px" height="32px" viewBox="0 0 20 20" fill="#df0016" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M5.5 10a.5.5 0 01.5-.5h8a.5.5 0 010 1H6a.5.5 0 01-.5-.5z" clip-rule="evenodd"/>
                                        </svg>
                                    </a>
                                </div>
                        
                                <div class="col-5 pr-0">
                                    <div class="progress bg-danger pr-0 border" style="border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important;">
                                        <div class="progress-bar bg-light" role="progressbar" style="width: 100%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="col-5 pl-0">
                                    <div class="progress bg-light pl-0 border" style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 60%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="col-1 p-0" style="margin-top: -8px; margin-left: -12px">
                                    <a style="display: inline-block; height: 32px; width: 32px;">
                                        <svg class="bi bi-plus" width="32px" height="32px" viewBox="0 0 20 20" fill="#28a745" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M10 5.5a.5.5 0 01.5.5v4a.5.5 0 01-.5.5H6a.5.5 0 010-1h3.5V6a.5.5 0 01.5-.5z" clip-rule="evenodd"/>
                                            <path fill-rule="evenodd" d="M9.5 10a.5.5 0 01.5-.5h4a.5.5 0 010 1h-3.5V14a.5.5 0 01-1 0v-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- Exemple Salle Fin -->

                <div class="py-5"></div>

                <!-- Next -->
                <div class="fixed-bottom">
                    <a href="?next" class="btn btn-block btn-primary" style="height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 0px">
                        <svg class="bi bi-arrow-right" width="3em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M12.146 6.646a.5.5 0 01.708 0l3 3a.5.5 0 010 .708l-3 3a.5.5 0 01-.708-.708L14.793 10l-2.647-2.646a.5.5 0 010-.708z" clip-rule="evenodd"/>
                            <path fill-rule="evenodd" d="M4 10a.5.5 0 01.5-.5H15a.5.5 0 010 1H4.5A.5.5 0 014 10z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <div class="progress" style="border-top: 2px solid; border-top-color: white; border-radius: 0px">
                        <div class="progress-bar niveau" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

            </div>
<?php
}
else if (isset($_SESSION['salles']['tuto_step']) && $_SESSION['salles']['tuto_step'] == 3)
{
?>
            <!-- ETAPE 3 -->
            <div class="my-5 pb-5 px-3">
                <div style="padding-bottom: 5px; border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: white;">
                    <span><b>VALEUR DE CONFIANCE</b></span>
                </div>
                <div class="mt-2">
                    <span>Cette barre est l'atout principal de LaSalle. Plus elle est verte plus la salle est susceptible d'être ouverte, plus elle est rouge et plus elle a de chances d'être fermée.
                    </br>En cliquant à gauche de la barre, vous indiquez que la salle est fermée, et en cliquant à droite qu'elle est ouverte.
                    </br>Sa valeur se conserve de semaine en semaine, ce qui signifie qu'en signalant si la salle est ouverte ou non un jour, vous pouvez l'influencer durablement et votre participation restera utile.
                    </span>
                </div>


                <!-- Exemple Salle -->
                <div class="container-fluid salle mt-4">
                    <div class="row">

                        <!--Numéro & Horaires-->
                        <div class="col-6 border-right border-white" style="height: 82px;">
                            <div class="text-center" style="margin-top: -17px;">
                                <p style="line-height: 15px;">
                                    <span class="text-primary font-weight-bold m-0 display-4" style="opacity: 0.2">301</span></br>
                                        <span class="text-secondary display-5 m-0 p-0" style="opacity: 0.2">12h - 13h</span></br>
                                        <small class="text-secondary m-0 p-0" style="opacity: 0.2">Pleine</small></br>
                                </p>
                                    </div>
                        </div>

                        <!--J'y Vais-->
                        <div class="col-6 border-left border-white" style="height: 82px;">
                            <div style="padding-top: 22px;">
                                <div class="pb-2" style="display: flex; justify-content: right; opacity: 0.2">
                                    <button class="btn btn-success btn-block align-self-center">J'y vais</button>
                                </div>
                            </div>
                        </div>

                        <!--Trust Bar-->
                        <div class="container-fluid mt-3">
                            <div class="row">
                                <div class="col-1 pr-0" style="margin-top: -8px; margin-left: -10px; margin-right: 10px">
                                    <a style="display: inline-block; height: 32px; width: 32px;">
                                        <svg class="bi bi-dash" width="32px" height="32px" viewBox="0 0 20 20" fill="#df0016" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M5.5 10a.5.5 0 01.5-.5h8a.5.5 0 010 1H6a.5.5 0 01-.5-.5z" clip-rule="evenodd"/>
                                        </svg>
                                    </a>
                                </div>
                        
                                <div class="col-5 pr-0">
                                    <div class="progress bg-danger pr-0 border" style="border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important;">
                                        <div class="progress-bar bg-light" role="progressbar" style="width: 100%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="col-5 pl-0">
                                    <div class="progress bg-light pl-0 border" style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 60%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="col-1 p-0" style="margin-top: -8px; margin-left: -12px">
                                    <a style="display: inline-block; height: 32px; width: 32px;">
                                        <svg class="bi bi-plus" width="32px" height="32px" viewBox="0 0 20 20" fill="#28a745" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M10 5.5a.5.5 0 01.5.5v4a.5.5 0 01-.5.5H6a.5.5 0 010-1h3.5V6a.5.5 0 01.5-.5z" clip-rule="evenodd"/>
                                            <path fill-rule="evenodd" d="M9.5 10a.5.5 0 01.5-.5h4a.5.5 0 010 1h-3.5V14a.5.5 0 01-1 0v-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- Exemple Salle Fin -->

                <div class="py-5"></div>

                <!-- Next -->
                <div class="fixed-bottom">
                    <a href="?next" class="btn btn-block btn-primary" style="height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 0px">
                        <svg class="bi bi-arrow-right" width="3em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M12.146 6.646a.5.5 0 01.708 0l3 3a.5.5 0 010 .708l-3 3a.5.5 0 01-.708-.708L14.793 10l-2.647-2.646a.5.5 0 010-.708z" clip-rule="evenodd"/>
                            <path fill-rule="evenodd" d="M4 10a.5.5 0 01.5-.5H15a.5.5 0 010 1H4.5A.5.5 0 014 10z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <div class="progress" style="border-top: 2px solid; border-top-color: white; border-radius: 0px">
                        <div class="progress-bar niveau" role="progressbar" style="width: 16.66%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

            </div>

<?php
}
else if (isset($_SESSION['salles']['tuto_step']) && $_SESSION['salles']['tuto_step'] == 4)
{
?>
            <!-- ETAPE 4 -->
            <div class="my-5 pb-5 px-3">
                <div style="padding-bottom: 5px; border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: white;">
                    <span><b>POPULATION</b></span>
                </div>
                <div class="mt-2">
                    <span>La dernière information représente le nombre de personne déjà présentes dans la salle. Elle comporte 5 niveaux : Bourges, Limoges, Clermont, Bordeaux, et Paris.
                    </br>Pour garder cette échelle représentantative de la réalité, il convient de cliquer sur le bouton "J'y vais" lorsque vous vous installez dans une salle.
                    </span>
                </div>


                <!-- Exemple Salle -->
                <div class="container-fluid salle mt-4">
                    <div class="row">

                        <!--Numéro & Horaires-->
                        <div class="col-6 border-right border-white" style="height: 82px;">
                            <div class="text-center" style="margin-top: -17px;">
                                <p style="line-height: 15px;">
                                    <span class="text-primary font-weight-bold m-0 display-4" style="opacity: 0.2">301</span></br>
                                        <span class="text-secondary display-5 m-0 p-0" style="opacity: 0.2">12h - 13h</span></br>
                                        <small class="text-secondary m-0 p-0">Bourges</small></br>
                                </p>
                                    </div>
                        </div>

                        <!--J'y Vais-->
                        <div class="col-6 border-left border-white" style="height: 82px;">
                            <div style="padding-top: 22px;">
                                <div class="pb-2" style="display: flex; justify-content: right">
                                    <button class="btn btn-success btn-block align-self-center">J'y vais</button>
                                </div>
                            </div>
                        </div>

                        <!--Trust Bar-->
                        <div class="container-fluid mt-3" style="opacity: 0.2">
                            <div class="row">
                                <div class="col-1 pr-0" style="margin-top: -8px; margin-left: -10px; margin-right: 10px">
                                    <a style="display: inline-block; height: 32px; width: 32px;">
                                        <svg class="bi bi-dash" width="32px" height="32px" viewBox="0 0 20 20" fill="#df0016" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M5.5 10a.5.5 0 01.5-.5h8a.5.5 0 010 1H6a.5.5 0 01-.5-.5z" clip-rule="evenodd"/>
                                        </svg>
                                    </a>
                                </div>
                        
                                <div class="col-5 pr-0">
                                    <div class="progress bg-danger pr-0 border" style="border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important;">
                                        <div class="progress-bar bg-light" role="progressbar" style="width: 100%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="col-5 pl-0">
                                    <div class="progress bg-light pl-0 border" style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 60%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="col-1 p-0" style="margin-top: -8px; margin-left: -12px">
                                    <a style="display: inline-block; height: 32px; width: 32px;">
                                        <svg class="bi bi-plus" width="32px" height="32px" viewBox="0 0 20 20" fill="#28a745" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M10 5.5a.5.5 0 01.5.5v4a.5.5 0 01-.5.5H6a.5.5 0 010-1h3.5V6a.5.5 0 01.5-.5z" clip-rule="evenodd"/>
                                            <path fill-rule="evenodd" d="M9.5 10a.5.5 0 01.5-.5h4a.5.5 0 010 1h-3.5V14a.5.5 0 01-1 0v-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- Exemple Salle Fin -->

                <div class="py-5"></div>

                <!-- Next -->
                <div class="fixed-bottom">
                    <a href="?next" class="btn btn-block btn-primary" style="height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 0px">
                        <svg class="bi bi-arrow-right" width="3em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M12.146 6.646a.5.5 0 01.708 0l3 3a.5.5 0 010 .708l-3 3a.5.5 0 01-.708-.708L14.793 10l-2.647-2.646a.5.5 0 010-.708z" clip-rule="evenodd"/>
                            <path fill-rule="evenodd" d="M4 10a.5.5 0 01.5-.5H15a.5.5 0 010 1H4.5A.5.5 0 014 10z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <div class="progress" style="border-top: 2px solid; border-top-color: white; border-radius: 0px">
                        <div class="progress-bar niveau" role="progressbar" style="width: 33.32%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

            </div>

<?php
}
else if (isset($_SESSION['salles']['tuto_step']) && $_SESSION['salles']['tuto_step'] == 5)
{
?>           

            <!-- ETAPE 5 -->
            <div class="my-5 pb-5 px-3">
                <div style="padding-bottom: 5px; border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: white;">
                    <span><b>SOUMETTRE UNE SALLE</b></span>
                </div>
                <div class="mt-2">
                    <span>L'aspect communautaire est très important pour le fonctionnement de LaSalle, c'est pourquoi vous pouvez vous même soumettre une salle que vous trouvez ouverte alors qu'elle n'est pas indiquée.
                    </br>Remplissez un simple formulaire en indiquant le jour et l'heure et votre suggestion apparaîtra aux yeux de tous.<br>
                    <br>
                    Pour éviter que des salles au nom impromptu apparaissent dans la liste nous avons mis en place des restrictions.
                    Si votre salle n'est pas acceptée alors qu'elle devrait l'être, vous pouvez contacter un administrateur.
                    </span>
                </div>

                <!-- Next -->
                <div class="fixed-bottom">
                    <a href="?next" class="btn btn-block btn-primary" style="height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 0px">
                        <svg class="bi bi-arrow-right" width="3em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M12.146 6.646a.5.5 0 01.708 0l3 3a.5.5 0 010 .708l-3 3a.5.5 0 01-.708-.708L14.793 10l-2.647-2.646a.5.5 0 010-.708z" clip-rule="evenodd"/>
                            <path fill-rule="evenodd" d="M4 10a.5.5 0 01.5-.5H15a.5.5 0 010 1H4.5A.5.5 0 014 10z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <div class="progress" style="border-top: 2px solid; border-top-color: white; border-radius: 0px">
                        <div class="progress-bar niveau" role="progressbar" style="width: 50%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

            </div>

<?php
}
else if (isset($_SESSION['salles']['tuto_step']) && $_SESSION['salles']['tuto_step'] == 6)
{
?>  

            <!-- ETAPE 6 -->
            <div class="my-5 pb-5 px-3">
                <div style="padding-bottom: 5px; border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: white;">
                    <span><b>NIVEAUX & PARTENAIRE</b></span>
                </div>
                <div class="mt-2">
                    <span>Votre engagement est matérialisé par votre niveau. Plus vous montez plus vos interactions auront de l'importance.
                    </br>En atteignant le niveau 5, votre participation est récompensée en vous accordant le grade Partenaire, qui fait de vous un membre de confiance de la communauté.
                    </span>
                </div>

                <!-- Niveau Exemple -->
                <div class="mt-5">
                    <div style="margin-bottom: -10px;">
                        <span><b>NIVEAU 5</b></span>
                    </div>
                    <div class="progress" style="margin-top: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: 80%; background: linear-gradient(90deg, rgba(23,162,184,1) 0%, rgba(23,162,184,1) 25%, rgba(228,28,183,1) 100%)" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <!-- Next -->
                <div class="fixed-bottom">
                    <a href="?next" class="btn btn-block btn-primary" style="height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 0px">
                        <svg class="bi bi-arrow-right" width="3em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M12.146 6.646a.5.5 0 01.708 0l3 3a.5.5 0 010 .708l-3 3a.5.5 0 01-.708-.708L14.793 10l-2.647-2.646a.5.5 0 010-.708z" clip-rule="evenodd"/>
                            <path fill-rule="evenodd" d="M4 10a.5.5 0 01.5-.5H15a.5.5 0 010 1H4.5A.5.5 0 014 10z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <div class="progress" style="border-top: 2px solid; border-top-color: white; border-radius: 0px">
                        <div class="progress-bar niveau" role="progressbar" style="width: 66.66%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

            </div>


<?php
}
else if (isset($_SESSION['salles']['tuto_step']) && $_SESSION['salles']['tuto_step'] == 7)
{
?>  
            
            <!-- ETAPE 7 -->
            <div class="my-5 pb-5 px-3">
                <div style="padding-bottom: 5px; border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: white;">
                    <span><b>MODERATION</b></span>
                </div>
                <div class="mt-2">
                    <span>Dans le monde des bisounours, LaSalle fonctionnerait sans doute parfaitement. Malheureusement ce n'est pas le cas, c'est pourquoi les utilisateurs détectés comme nocifs à la communauté feront l'objet de bannissements.</span>
                </div>

                <!-- Next -->
                <div class="fixed-bottom">
                    <a href="?next" class="btn btn-block btn-primary" style="height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 0px">
                        <svg class="bi bi-arrow-right" width="3em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M12.146 6.646a.5.5 0 01.708 0l3 3a.5.5 0 010 .708l-3 3a.5.5 0 01-.708-.708L14.793 10l-2.647-2.646a.5.5 0 010-.708z" clip-rule="evenodd"/>
                            <path fill-rule="evenodd" d="M4 10a.5.5 0 01.5-.5H15a.5.5 0 010 1H4.5A.5.5 0 014 10z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <div class="progress" style="border-top: 2px solid; border-top-color: white; border-radius: 0px">
                        <div class="progress-bar niveau" role="progressbar" style="width: 83.32%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

            </div>

<?php
}
else if (isset($_SESSION['salles']['tuto_step']) && $_SESSION['salles']['tuto_step'] == 8)
{
?>  

            <!-- ETAPE 8 -->
            <div class="my-5 pb-5 px-3">
                <div style="padding-bottom: 5px; border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: white;">
                    <span><b>VOUS ÊTES PRÊTS !</b></span>
                </div>
                <div class="mt-2">
                    <span>Nous espérons que vous apprécierez LaSalle et que vous trouverez le service utile.</span>
                </div>
                <div class="container mb-1 mt-4" style="border-top-style: solid; border-top-width: 1px; border-top-color: white;">
                </div>  
                
                <form method="post" action="">

                    <div>
                        <p style="line-height: 15px; font-size: 0.7em;">Afin de vérifier que vous êtes bien un étudiant du lycée Blaise Pascal merci de répondre à la question suivante:<br>
                        <u>Dans quel batiment (son nom) se trouve la salle 40 ?</u><br>
                        Ecrivez votre réponse sans espaces ou tirets.</p>
                        <input name="pass" type="text" class="form-control text-center" placeholder="C*****" style="height: 50px;" required></input>
                    </div>

                    <div style="width:100%; display:flex; justify-content:center; margin-bottom: 40px;" class="my-3">
                        <div class="g-recaptcha" data-sitekey="<?php echo $public_key; ?>"></div>
                    </div>  
                    
                    <!-- Crédits -->
                    <div class="pb-1 mx-5 mt-2" style="border-bottom: 1px solid; border-bottom-color: white; display: flex; align-items: center; justify-content: center">
                        <span><b>CRÉDITS</b></span>
                    </div>
                    <div class="mt-2" style="display: flex; align-items: center; justify-content: center">
                        <span>DESCORSIERS Thomas
                        </br>BOUSSAROQUE Alexis</span>
                    </div>    

                    <!-- Next -->
                    <div class="fixed-bottom">
                        <div class="mb-2 text-secondary" style="display: flex; align-items: center; justify-content: center">
                            <span><small>DÉCEMBRE 2019 - MPSI 1</small></span>
                        </div>
                        <button type="submit" name="go" class="btn btn-block btn-primary" style="height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 0px">
                            <span><b>C'EST PARTI !</b></span>
                        </button>
                        <div class="progress" style="border-top: 2px solid; border-top-color: white; border-radius: 0px">
                            <div class="progress-bar niveau" role="progressbar" style="width: 100%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </form>

            </div>


<?php
}
?>


        </div>
    </body>
</html>