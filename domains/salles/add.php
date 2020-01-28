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
$jour = gestion_salles::getCurrentJour();
$heure = gestion_salles::getCurrentHeure();
if (!(gestion_salles::verifHeure($heure) && $jour))
{
    unset($heure);
    unset($jour);
}

if (isset($_GET['add']) && isset($_GET['salle']) && isset($_GET['jour']) && isset($_GET['heure']))
{
    unset($heure);
    unset($jour);

    $add = $gestion_salles->addSalle($_GET['salle'], $_GET['jour'], gestion_salles::heure_int2string($_GET['heure']));
    if (err::c($add))
    {
        $_SESSION['salleop']['confirm_add'] = [
            'salle' => $_GET['salle'],
            'jour' => $_GET['jour'],
            'heure' => $_GET['heure']
        ];
        header('Location: https://salles.bde-bp.fr/search');
    }
    else
    {
        if ($add->g() == err::SALLE_EXISTE)
        {
            $s_erreur = "Cette salle a déjà été soumise.";
        }
        else if ($add->g() == err::BAD_SALLE)
        {
            $s_erreur = "Cette salle n'existe pas.";
        }
        else
        {
            $erreur = "Une erreur est survenue.";
        }
    }
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
        <div class="container-fluid p-0" style="max-width: 600px; min-width: 200px;">
            <!-- Header -->
            <header class="fixed-top container-fluid p-0" style="max-width: 640px;">
                <div class="pb-4 header">
                    <div class="row p-0">
                        <div class="col-3"></div>
                        <div class="col-6 pt-0">
                            <svg id="Calque_1" data-name="Calque 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 462.55 85.88"><defs><style>.cls-1{fill:#fff;}</style></defs><title>LaSalle-LogoBlanc</title><path class="cls-1" d="M.77,85.53,12.44,2h3L4.07,82.58H45.83l-.35,3Z" transform="translate(-0.77 -0.83)"/><path class="cls-1" d="M116,85.53h-3.19L106,55.69H67.42L52.67,85.53H49.49L91.84,2h5.31Zm-10.5-32.79L94.67,4.25H94L69,52.74Z" transform="translate(-0.77 -0.83)"/><path class="cls-1" d="M137.14,59.11c3.65,5.31,10.61,8,16.87,8,5.66,0,10.61-2,11.44-6.37.94-5.19-6.37-7.43-13.1-8.38C139.26,50.26,127.46,39.76,130.41,24,133.6,6.85,148.7.83,164,.83c10.15,0,19.59,2.83,26.78,13.33L175.48,24.78c-3.54-4-9.09-5.9-13.92-6-5.31-.11-9.8,1.89-10.27,6.14-.59,4.83,3.66,7,10,8.37,14.51,2.72,28.67,8.26,24.77,29C183,78.57,168.87,86.71,150.82,86.71c-10,0-22.41-5.07-28.78-15.1Z" transform="translate(-0.77 -0.83)"/><path class="cls-1" d="M261.36,85.53H238.71l-3.19-16.75H213.23l-7.44,16.75H183.15L224,2h19.23ZM233.16,50l-2.47-24.41h-.83L220.66,50Z" transform="translate(-0.77 -0.83)"/><path class="cls-1" d="M278.46,2H299.7l-8.85,62.64h34L322,85.53h-55.2Z" transform="translate(-0.77 -0.83)"/><path class="cls-1" d="M341.81,2h21.24L354.2,64.65h34l-2.83,20.88H330.13Z" transform="translate(-0.77 -0.83)"/><path class="cls-1" d="M421.91,34.33h33.86l-2.6,18.76H419.32L417.55,65.6h36.8l-2.71,19.93H393.48L405.16,2h58.16L460.49,22H423.68Z" transform="translate(-0.77 -0.83)"/></svg>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Message -->
            <div class="pt-5 mt-5 px-4">
                <span style="display: flex; justify-content: center;"><b>MESSAGE</b></span>
                <div class="container mt-2 mb-1" style="border-top-style: solid; border-top-width: 1px; border-top-color: white;">
                </div>
                <p class="pt-2 px-3" style="line-height: 20px; text-align: center">LaSalle est un service communautaire.
                Afin de pouvoir en profiter pleinement et de ne pas déteriorer inutilement l'expérience des autres utilisateurs,
                merci de ne pas partager volontairement des informations erronées.</p>
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

            <!-- Formulaire -->
            <div class="container-fluid py-3 px-4 mb-5">

                <form method="get"> 

                    <div class="container-fluid border border-white salle bg-dark mb-3">
                      
                        <!-- Jour -->
                        <div class="row">
                            <div class="col-5" style="padding-top: 6px;">
                                <p class="text-white">Jour</p>
                            </div>
                            <div class="col-7"> 
                                <select required class="form-control" name="jour">
                                    <option disabled="disabled" <?php if (!isset($jour)) { echo 'selected="selected" default'; }?>>JOUR</option>
                                    <option value="lundi" <?php if (isset($jour) && $jour == "lundi") { echo 'selected="selected" default'; }?>>LUNDI</option>
                                    <option value="mardi" <?php if (isset($jour) && $jour == "mardi") { echo 'selected="selected" default'; }?>>MARDI</option>
                                    <option value="mercredi" <?php if (isset($jour) && $jour == "mercredi") { echo 'selected="selected" default'; }?>>MERCREDI</option>
                                    <option value="jeudi" <?php if (isset($jour) && $jour == "jeudi") { echo 'selected="selected" default'; }?>>JEUDI</option>
                                    <option value="vendredi" <?php if (isset($jour) && $jour == "vendredi") { echo 'selected="selected" default'; }?>>VENDREDI</option>
                                    <option value="samedi" <?php if (isset($jour) && $jour == "samedi") { echo 'selected="selected" default'; }?>>SAMEDI</option>
                                </select>
                            </div>
                        </div>
                    
                        <!-- Heure -->
                        <div class="row">
                            <div class="col-5" style="padding-top: 6px;">
                                <p class="text-white">Plage horaire</p>
                            </div>
                            <div class="col-7"> 
                                <select required class="form-control" name="heure">
                                    <option disabled="disabled" <?php if (!isset($heure)) { echo 'selected="selected" default'; }?>>PLAGE HORAIRE</option>
                                    <option value="8" <?php if (isset($heure) && $heure == 8) { echo 'selected="selected" default'; }?>>8H - 9H</option>
                                    <option value="9" <?php if (isset($heure) && $heure == 9) { echo 'selected="selected" default'; }?>>9H - 10H</option>
                                    <option value="10" <?php if (isset($heure) && $heure == 10) { echo 'selected="selected" default'; }?>>10H - 11H</option>
                                    <option value="11" <?php if (isset($heure) && $heure == 11) { echo 'selected="selected" default'; }?>>11H - 12H</option>
                                    <option value="12" <?php if (isset($heure) && $heure == 12) { echo 'selected="selected" default'; }?>>12H - 13H</option>
                                    <option value="13" <?php if (isset($heure) && $heure == 13) { echo 'selected="selected" default'; }?>>13H - 14H</option>
                                    <option value="14" <?php if (isset($heure) && $heure == 14) { echo 'selected="selected" default'; }?>>14H - 15H</option>
                                    <option value="15" <?php if (isset($heure) && $heure == 15) { echo 'selected="selected" default'; }?>>15H - 16H</option>
                                    <option value="16" <?php if (isset($heure) && $heure == 16) { echo 'selected="selected" default'; }?>>16H - 17H</option>
                                    <option value="17" <?php if (isset($heure) && $heure == 17) { echo 'selected="selected" default'; }?>>17H - 18H</option>
                                    <option value="18" <?php if (isset($heure) && $heure == 18) { echo 'selected="selected" default'; }?>>18H - 19H</option>
                                </select>
                            </div>
                        </div>

                        <div class="container-fluid p-0 pt-3">

                            <input name="salle" type="text" class="form-control text-center" placeholder="Salle" style="height: 50px;" required></input>

                        </div>

                        <?php
                        if (isset($s_erreur))
                        {
                        ?>
                            
                            <div class="container-fluid">
                                <p class="text-danger mb-0" style="display: flex; justify-content: center;"><?php echo $s_erreur; ?></p>
                            </div>
                                
                        <?php
                        }
                        ?>

                    
                    </div>   

                    <div style="display: flex; justify-content: center;" class="mb-5">
                        <button type="submit" name="add" class="btn btn-primary" style="width: 200px">Soumettre</button>
                    </div>

                </form>
                
            </div>

            <!-- Retour -->
            <div class="container-fluid fixed-bottom py-4 bg-dark mt-5">
                <a href="https://salles.bde-bp.fr/search" class="mt-2 text-white">
                    <svg class="bi bi-chevron-compact-left" style="margin-top: -2px" width="20px"viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M11.224 3.553a.5.5 0 01.223.67L8.56 10l2.888 5.776a.5.5 0 11-.894.448l-3-6a.5.5 0 010-.448l3-6a.5.5 0 01.67-.223z" clip-rule="evenodd"/>
                    </svg>
                    RETOUR
                </a>
            </div>
        </div>
    </body>
</html>