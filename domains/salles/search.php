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


/**
 * RECHERCHE
 */



$jour = gestion_salles::getCurrentJour();

if (isset($_GET['op']))
{
    $jour = $_GET['op'];
    $op = true;
}

if ($gestion_adherents->isConnected() && $gestion_adherents->authRole($_SESSION['Adherent'], adherent::ROLE_ADMIN))
{
    $admin = true;

    if (isset($_GET['reset']))
    {
        $idh = $_GET['reset'];

        $gestion_salles->resetTrust($idh);
    }

    if (isset($_GET['del']))
    {
        $del = unserialize(urldecode($_GET['del']));

        $gestion_salles->deleteSalle($del['s'], $del['j'], $del['h']);
    }

    if (isset($_GET['ban']))
    {
        $idc = $_GET['ban'];

        $gestion_salles->ban($idc);

    }

}

if ($jour && gestion_salles::verifJour($jour))
{
    $h_current = gestion_salles::getCurrentHeure();
    
    // Recuperer l'heure depuis le get si elle est valide
    if (
        isset($_GET['h'])
        && (isBetween((int) $_GET['h'], 8, 18)
        && isBetween((int) $_GET['h'], gestion_salles::getCurrentHeure() - 1, gestion_salles::getCurrentHeure() + 1))
        || isset($op)
        )
    {
        $heure = (int) $_GET['h'];
    }
    else
    {
        $heure = $h_current;
    }



    $heure = gestion_salles::heure_int2string($heure);

    if (gestion_salles::verifHeure($heure))
    {
        // Affichage et gestion des liens
        $h_start = gestion_salles::heure_string2int($heure);
        $h_stop = $h_start + 1;

        // Chevron de recherche
        if (($h_start > $h_current - 1) && gestion_salles::verifHeure($h_start - 1))
        {
            $h_before = $h_start - 1;
        }
        if (($h_start < $h_current + 1) && gestion_salles::verifHeure($h_start + 1))
        {
            $h_after = $h_start + 1;
        }
        // pour maintenir la recherche pdt les actions
        $slink = "?h=".$h_start;
        if (isset($admin))
        {
            $slink .= "&op=".$jour;
        }




        // Recherche
        $salles = $gestion_salles->getSalles($jour, $heure);
        if (!err::c($salles))
        {
            if ($salles->g() == err::NO_SALLES)
            {
                unset($salles);
            }
            else
            {
                $erreur = "Une erreur est survenue.";
            }
        }
    }
    else
    {
        $disabled = true;
        header('Location: https://salles.bde-bp.fr');
        die();
    }
}
else
{
    header('Location: https://salles.bde-bp.fr');
    die();
}


// add trust
if (!isset($disabled) && isset($_GET['trust']) && isset($_GET['salle']) && isset($_GET['jour']) && isset($_GET['heure']))
{
    if (
        isBetween((int) $_GET['heure'], 8, 18)
        && isBetween((int) $_GET['heure'], gestion_salles::getCurrentHeure() - 1, gestion_salles::getCurrentHeure() + 1)
        && gestion_salles::verifJour($_GET['jour'])
        && $_GET['jour'] == gestion_salles::getCurrentJour()
        && $gestion_salles->canUpdate(gestion_salles::UPDATE_TRUST, $_GET['salle'], $_GET['jour'], gestion_salles::heure_int2string($_GET['heure']))
        )
    {
        if ($_GET['trust'] == 'add')
        {
            if($gestion_salles->updateSalle($_GET['salle'], $_GET['jour'], gestion_salles::heure_int2string($_GET['heure']), gestion_salles::UPDATE_TRUST, true))
            {
                header('Location: '.$slink);
            }
            else
            {
                $erreur = "Une erreur est survenue.";
            }
        }
        else if ($_GET['trust'] == 'remove')
        {
            if($gestion_salles->updateSalle($_GET['salle'], $_GET['jour'], gestion_salles::heure_int2string($_GET['heure']), gestion_salles::UPDATE_TRUST, false))
            {
                header('Location: '.$slink);
            }
            else
            {
                $erreur = "Une erreur est survenue.";
            }
        }
    }
    else
    {
        $erreur = "Tu ne peux pas faire ça!";
    }
}

// add pop
if (!isset($disabled) && isset($_GET['pop']) && isset($_GET['salle']) && isset($_GET['jour']) && isset($_GET['heure']))
{
    if (
        isBetween((int) $_GET['heure'], 8, 18)
        && isBetween((int) $_GET['heure'], gestion_salles::getCurrentHeure() - 1, gestion_salles::getCurrentHeure() + 1)
        && gestion_salles::verifJour($_GET['jour'])
        && $_GET['jour'] == gestion_salles::getCurrentJour()
        && $gestion_salles->canUpdate(gestion_salles::UPDATE_POP, $_GET['salle'], $_GET['jour'], gestion_salles::heure_int2string($_GET['heure']))
        )
    {
        if ($_GET['pop'] == 'add')
        {
            if($gestion_salles->updateSalle($_GET['salle'], $_GET['jour'], gestion_salles::heure_int2string($_GET['heure']), gestion_salles::UPDATE_POP, true))
            {
                header('Location: '.$slink);
            }
            else
            {
                $erreur = "Une erreur est survenue.";
            }
        }
        else if ($_GET['pop'] == 'remove')
        {
            if($gestion_salles->updateSalle($_GET['salle'], $_GET['jour'], gestion_salles::heure_int2string($_GET['heure']), gestion_salles::UPDATE_POP, false))
            {
                header('Location: '.$slink);
            }
            else
            {
                $erreur = "Une erreur est survenue.";
            }
        }
    }
    else
    {
        $erreur = "Tu ne peux pas faire ça!";
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
            <header class="fixed-top container-fluid p-0" style="max-width: 640px;">
                <div class="header">
                    <a class="row p-0" href="https://salles.bde-bp.fr">
                        <div class="col-3"></div>
                        <div class="col-6 pt-0">
                            <svg id="Calque_1" data-name="Calque 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 462.55 85.88"><defs><style>.cls-1{fill:#fff;}</style></defs><title>LaSalle-LogoBlanc</title><path class="cls-1" d="M.77,85.53,12.44,2h3L4.07,82.58H45.83l-.35,3Z" transform="translate(-0.77 -0.83)"/><path class="cls-1" d="M116,85.53h-3.19L106,55.69H67.42L52.67,85.53H49.49L91.84,2h5.31Zm-10.5-32.79L94.67,4.25H94L69,52.74Z" transform="translate(-0.77 -0.83)"/><path class="cls-1" d="M137.14,59.11c3.65,5.31,10.61,8,16.87,8,5.66,0,10.61-2,11.44-6.37.94-5.19-6.37-7.43-13.1-8.38C139.26,50.26,127.46,39.76,130.41,24,133.6,6.85,148.7.83,164,.83c10.15,0,19.59,2.83,26.78,13.33L175.48,24.78c-3.54-4-9.09-5.9-13.92-6-5.31-.11-9.8,1.89-10.27,6.14-.59,4.83,3.66,7,10,8.37,14.51,2.72,28.67,8.26,24.77,29C183,78.57,168.87,86.71,150.82,86.71c-10,0-22.41-5.07-28.78-15.1Z" transform="translate(-0.77 -0.83)"/><path class="cls-1" d="M261.36,85.53H238.71l-3.19-16.75H213.23l-7.44,16.75H183.15L224,2h19.23ZM233.16,50l-2.47-24.41h-.83L220.66,50Z" transform="translate(-0.77 -0.83)"/><path class="cls-1" d="M278.46,2H299.7l-8.85,62.64h34L322,85.53h-55.2Z" transform="translate(-0.77 -0.83)"/><path class="cls-1" d="M341.81,2h21.24L354.2,64.65h34l-2.83,20.88H330.13Z" transform="translate(-0.77 -0.83)"/><path class="cls-1" d="M421.91,34.33h33.86l-2.6,18.76H419.32L417.55,65.6h36.8l-2.71,19.93H393.48L405.16,2h58.16L460.49,22H423.68Z" transform="translate(-0.77 -0.83)"/></svg>
                        </div>
                    </a>

                    <!-- Heure -->
                    <div class="row m-0 pt-1">
                        <?php if (!isset($disabled))
                        {
                        ?>
                        <div class="col-4 text-right px-0">
                            <?php if (isset($h_before)) { ?>
                            <a href="?<?php if (isset($admin)) { echo "op=".$jour."&"; }?>h=<?php echo $h_before;?>" class="text-white">
                                <svg class="bi bi-chevron-left" style="margin-top: -2px" width="1.5em" height="1.5em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M13.354 3.646a.5.5 0 010 .708L7.707 10l5.647 5.646a.5.5 0 01-.708.708l-6-6a.5.5 0 010-.708l6-6a.5.5 0 01.708 0z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                            <?php } ?>
                        </div>
                        <div class="col-4 text-center px-0">
                            <span><?php echo $h_start."H - ".$h_stop."H"; ?></span>
                        </div>
                        <div class="col-4 text-left px-0">
                        <?php if (isset($h_after)) { ?>
                            <a href="?<?php if (isset($admin)) { echo "op=".$jour."&"; }?>h=<?php echo $h_after;?>" class="text-white">
                                <svg class="bi bi-chevron-right" style="margin-top: -2px" width="1.5em" height="1.5em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M6.646 3.646a.5.5 0 01.708 0l6 6a.5.5 0 010 .708l-6 6a.5.5 0 01-.708-.708L12.293 10 6.646 4.354a.5.5 0 010-.708z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                            <?php } ?>
                        </div>
                        <?php
                        }
                        ?>
                    </div>


                </div>
            </header>

            <div class="container-fluid" style="margin-top: 110px;">
                <div class="container py-2 px-2 mb-5 pb-5">

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

    <?php
    if (isset($_SESSION['salleop']['confirm_add']))
    {
    ?>
            
            <div class="alert alert-success">
                Vous avez rajouté une salle avec succès:<br>
                Salle: <?php echo $_SESSION['salleop']['confirm_add']['salle']; ?><br>
                Jour: <?php echo $_SESSION['salleop']['confirm_add']['jour']; ?><br>
                Heure: <?php echo $_SESSION['salleop']['confirm_add']['heure']; ?><br>
            </div>
            
    <?php
    unset($_SESSION['salleop']['confirm_add']);
    }
    ?>
        
                        
                    <!--Salles-->
                    <div class="container-fluid p-0">

    <?php
    if (!isset($disabled) && isset($salles))
    {
        foreach($salles as $salle)
        {
            $nom = $salle->getNom();
            $p = $salle->getPlage();
            $plage = $p['debut']."h - ".($p['fin'] + 1)."h";
            $trust = $salle->getTrust();
            $pop = $salle->getPop();


            // Section admin
            $idh = $salle->getIDH();
            $del_infos = urlencode(serialize([
                's' => $nom,
                'j' => $jour,
                'h' => $heure
            ]));
            $idc = $salle->getContributeur();

            // Affichage des boutons
            if ($gestion_salles->canUpdate(gestion_salles::UPDATE_TRUST, $nom, $jour, $heure))
            {
                $tlink = "&salle=".$nom."&jour=".$jour."&heure=".gestion_salles::heure_string2int($heure);
            }
            else
            {
                unset($tlink);
            }
            if ($gestion_salles->canUpdate(gestion_salles::UPDATE_POP, $nom, $jour, $heure))
            {
                $plink = "&salle=".$nom."&jour=".$jour."&heure=".gestion_salles::heure_string2int($heure);
            }
            else
            {
                unset($plink);
            }

            // Affichage de la trustbar
            if ($trust > 100)
            {
                $trust_positive = $trust - 100;
                $trust_negative = 100;
            }
            else if ($trust < 100)
            {
                $trust_positive = 0;
                $trust_negative = $trust;
            }
            else
            {
                $trust_positive = 0;
                $trust_negative = 100;
            }

            // Affichage de la pop
            if (isBetween($pop, 30, 60))
            {
                $pop_aff = "Moyennement remplie";
            }
            else if ($pop >= 60)
            {
                $pop_aff = "Plutôt pleine";
            }
            else
            {
                $pop_aff = "Plutôt vide";
            }
    ?>

                            <!--Une Salle-->
                            <div class="container-fluid salle mb-3">

                                <div class="row">

                                    <!--Numéro & Horaires-->
                                    <div class="col-6" style="height: 82px; border-right: 1px solid; border-color: rgb(99, 98, 98);"">
                                        <div class="text-center" style="margin-top: -17px;">
                                            <p style="line-height: 15px;">
                                                <span class="text-primary font-weight-bold m-0 display-4"><?php echo $nom; ?></span></br>
                                                    <span class="text-secondary display-5 m-0 p-0"><?php echo $plage; ?></span></br>
                                                    <?php
                                                        if (isset($admin))
                                                        {
                                                            ?>

                                                            <span class="text-secondary display-5 m-0 p-0"><?php echo $idh; ?></span> - <a href="<?php echo $slink; ?>&reset=<?php echo $idh; ?>">R</a> - <a href="<?php echo $slink; ?>&del=<?php echo $del_infos; ?>">D</a> - <a href="<?php echo $slink; ?>&ban=<?php echo $idc; ?>">B</a></br>

                                                            <?php
                                                        }
                                                    ?>
                                                    <small class="text-secondary m-0 p-0"><?php echo $pop_aff; ?></small></br>
                                            </p>
                                                </div>
                                    </div>
                                    <!-- FIN NUM -->

                                    <!--J'y Vais-->
                                    <div class="col-6" style="height: 82px; border-left: 1px solid; border-color: rgb(99, 98, 98);">
                                        <?php
                                            if (isset($plink))
                                            {
                                        ?>
                                            <div class="pb-2" style="display: flex; justify-content: right;">
                                                <a href="<?php echo $slink.$plink; ?>&pop=remove" class="btn btn-outline-warning btn-block align-self-center">Vide</a>
                                            </div>
                                            <div class="pb-2" style="display: flex; justify-content: right;">
                                                <a href="<?php echo $slink.$plink; ?>&pop=add" class="btn btn-outline-info btn-block align-self-center">Remplie</a>
                                            </div>
                                        <?php
                                            }
                                            else
                                            {
                                        ?>
                                            <p class="text-center text-muted mt-3">Vous avez déjà mis à jour cette salle.</p>
                                        <?php
                                            }
                                        ?>
                                    </div>
                                    <!-- FIN GO -->




                                    <!--Trust Bar-->
                                    <div class="container-fluid mt-3">
                                        <div class="row mt-1">
                                            <!-- <div class="col-1 pr-0" style="margin-top: -8px; margin-left: -10px; margin-right: 10px"> -->
                                            <div class="col-2 pr-1 text-left" style="margin-top: -8px;">
                                                <?php if (isset($tlink)) { ?>
                                                <a href="<?php echo $slink.$tlink; ?>&trust=remove" style="display: inline-block; height: 25px; width: 25px;">
                                                    <!-- <svg class="bi bi-dash" width="32px" height="32px" viewBox="0 0 20 20" fill="#df0016" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" d="M5.5 10a.5.5 0 01.5-.5h8a.5.5 0 010 1H6a.5.5 0 01-.5-.5z" clip-rule="evenodd"/>
                                                    </svg> -->
                                                    <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                            viewBox="0 0 52 52" style="enable-background:new 0 0 52 52;" xml:space="preserve"
                                                            width="25px" height="25px" fill="#df0016">
                                                        <path d="M26,0C11.664,0,0,11.663,0,26s11.664,26,26,26s26-11.663,26-26S40.336,0,26,0z M38.5,28h-25c-1.104,0-2-0.896-2-2
                                                            s0.896-2,2-2h25c1.104,0,2,0.896,2,2S39.604,28,38.5,28z"/>
                                                    </svg>
                                                </a>
                                                <?php } ?>
                                            </div>
                                    
                                            <div class="col-4 px-0">
                                                <div class="progress bg-danger pr-0 border" style="border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important;">
                                                    <div class="progress-bar bg-light" role="progressbar" style="width: <?php echo $trust_negative;?>%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="col-4 px-0">
                                                <div class="progress bg-light pl-0 border" style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $trust_positive;?>%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>

                                            <div class="col-2 pl-1 text-right" style="margin-top: -8px;">
                                                <?php if (isset($tlink)) { ?>
                                                <a href="<?php echo $slink.$tlink; ?>&trust=add" style="display: inline-block; height: 25px; width: 25px;">
                                                    <!-- <svg class="bi bi-plus" width="32px" height="32px" viewBox="0 0 20 20" fill="#28a745" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" d="M10 5.5a.5.5 0 01.5.5v4a.5.5 0 01-.5.5H6a.5.5 0 010-1h3.5V6a.5.5 0 01.5-.5z" clip-rule="evenodd"/>
                                                        <path fill-rule="evenodd" d="M9.5 10a.5.5 0 01.5-.5h4a.5.5 0 010 1h-3.5V14a.5.5 0 01-1 0v-4z" clip-rule="evenodd"/>
                                                    </svg> -->
                                                    <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                        viewBox="0 0 52 52" style="enable-background:new 0 0 52 52;" xml:space="preserve"
                                                        width="25px" height="25px" fill="#28a745">
                                                    <path d="M26,0C11.664,0,0,11.663,0,26s11.664,26,26,26s26-11.663,26-26S40.336,0,26,0z M38.5,28H28v11c0,1.104-0.896,2-2,2
                                                        s-2-0.896-2-2V28H13.5c-1.104,0-2-0.896-2-2s0.896-2,2-2H24V14c0-1.104,0.896-2,2-2s2,0.896,2,2v10h10.5c1.104,0,2,0.896,2,2
                                                        S39.604,28,38.5,28z"/>
                                                    </svg>
                                                </a>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- FIN TRUST -->



                                </div> <!-- fin row -->

                            </div>
                            <!-- FIN UNE SALLE -->

    <?php
        } // fin foreach
    } // fin isset salles
    else // pas de salles trouvées
    {
    ?>
                                <p class="muted text-center">
                                    <span class="display-1 font-weight-lighter">:'(</span><br><br>
                                    Roses are red, Violets are blue, Rooms are sweet, and I don't have any for you.<br>
                                    <small>Tu peux en soumettre une.</small>
                                </p>
    <?php
    }
    ?>


                    </div>

                    <!-- Soumettre -->
                    <div class="container-fluid fixed-bottom px-2 mb-4">
                        <div class="text-right">
                            <a href="https://salles.bde-bp.fr/add" class="p-3">
                                <svg height="2.5em" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 2977.63 500"><defs><style>.cls-1{fill:#fff;}.cls-2{fill:url(#Dégradé_sans_nom);}.cls-3{fill:url(#Dégradé_sans_nom-3);}.cls-4{fill:url(#Dégradé_sans_nom-5);}.cls-5{fill:url(#Dégradé_sans_nom-6);}.cls-6{fill:url(#Dégradé_sans_nom-8);}</style><linearGradient id="Dégradé_sans_nom" x1="178.33" y1="250" x2="2888.73" y2="250" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#e41c39"/><stop offset="0.25" stop-color="#e43534"/><stop offset="1" stop-color="#e4b31c"/></linearGradient><linearGradient id="Dégradé_sans_nom-3" x1="178.33" y1="249.58" x2="2888.73" y2="249.58" xlink:href="#Dégradé_sans_nom"/><linearGradient id="Dégradé_sans_nom-5" x1="178.33" y1="252.08" x2="2888.73" y2="252.08" xlink:href="#Dégradé_sans_nom"/><linearGradient id="Dégradé_sans_nom-6" x1="178.33" y1="249.58" x2="2888.73" y2="249.58" xlink:href="#Dégradé_sans_nom"/><linearGradient id="Dégradé_sans_nom-8" x1="178.33" y1="249.38" x2="2888.73" y2="249.38" xlink:href="#Dégradé_sans_nom"/></defs><title>LaSalle-Bouton-Soumettre</title><rect class="cls-1" width="2977.63" height="500" rx="250" ry="250"/><g id="Calque_2" data-name="Calque 2"><path class="cls-2" d="M2884.3,243.23v13.54a29.6,29.6,0,0,1-29.52,29.5H2769v85.8a29.61,29.61,0,0,1-29.51,29.51H2726a29.61,29.61,0,0,1-29.51-29.51v-85.8h-85.78a29.6,29.6,0,0,1-29.51-29.5V243.23a29.6,29.6,0,0,1,29.51-29.5h85.78V127.94A29.61,29.61,0,0,1,2726,98.42h13.55A29.61,29.61,0,0,1,2769,127.94v85.79h85.77A29.6,29.6,0,0,1,2884.3,243.23Z" transform="translate(-5.09 0)"/><path class="cls-2" d="M2884.3,243.23v13.54a29.6,29.6,0,0,1-29.52,29.5H2769v85.8a29.61,29.61,0,0,1-29.51,29.51H2726a29.61,29.61,0,0,1-29.51-29.51v-85.8h-85.78a29.6,29.6,0,0,1-29.51-29.5V243.23a29.6,29.6,0,0,1,29.51-29.5h85.78V127.94A29.61,29.61,0,0,1,2726,98.42h13.55A29.61,29.61,0,0,1,2769,127.94v85.79h85.77A29.6,29.6,0,0,1,2884.3,243.23Z" transform="translate(-5.09 0)"/></g><path class="cls-3" d="M216.54,303.57c29.48,35.72,94.27,37.38,96.34,5.82.83-16.62-26.16-26.17-50.25-29.49-47.34-7.48-90.53-37.38-90.53-93.44,0-57.72,52.33-88,106.32-88,35.71,0,70.18,10,100.5,46.93l-48.59,37.37c-30.73-32.8-83.48-31.56-84.72,2.49.83,14.12,16.19,22.43,39,27.41C337,222.18,389.3,240.45,386,312.71c-2.5,56.89-62.3,88-116.29,88-35.3,0-72.67-17.86-100.08-53.16Z" transform="translate(-5.09 0)"/><path class="cls-3" d="M410.89,199.33c0-67.69,60.63-100.91,121.68-100.91s122.09,33.64,122.09,100.91v100.5c0,67.28-60.63,100.92-121.68,100.92S410.89,367.11,410.89,299.83Zm74.34,100.5c0,21.6,24.08,32,47.75,32s47.35-10.8,47.35-32V199.33c0-22.84-24.51-33.22-48.59-33.22-23.26,0-46.51,11.63-46.51,33.22Z" transform="translate(-5.09 0)"/><path class="cls-4" d="M767.62,298.17c0,47.35,83.89,47.35,84.3,0V102.57h75.17v195.6c-.42,137.88-235.05,137.88-234.64,0V102.57h75.17Z" transform="translate(-5.09 0)"/><path class="cls-5" d="M971.1,102.57h74.34l61.88,143.69,62.29-143.69H1244v294H1169.2l.41-84.72,4.57-59.39-2.49-.41L1133.07,353H1082l-39.46-100.91-2.07.41,5,59.39V396.6H971.1Z" transform="translate(-5.09 0)"/><path class="cls-5" d="M1365.62,218h119.19v64.37H1365.62v44h129.57V396.6H1290.46v-294h204.73v69.77H1365.62Z" transform="translate(-5.09 0)"/><path class="cls-6" d="M1670.44,172.76V396.6h-75.16V172.76H1517.2v-70.6h231.32v70.6Z" transform="translate(-5.09 0)"/><path class="cls-6" d="M1913.38,172.76V396.6h-75.17V172.76h-78.07v-70.6h231.32v70.6Z" transform="translate(-5.09 0)"/><path class="cls-5" d="M2109,303.16h-13.29V396.6h-75.17v-294h117.53c60.63,0,107.56,27.83,109.22,96.76,0,54.41-22,85.14-57.73,96.35l80.15,100.92h-92.6Zm30.31-64c46.93,0,46.93-67.27,0-67.27h-43.6V239.2Z" transform="translate(-5.09 0)"/><path class="cls-5" d="M2364,218h119.19v64.37H2364v44h129.57V396.6H2288.8v-294h204.73v69.77H2364Z" transform="translate(-5.09 0)"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>