<?php

include('./content/panel_base.php');

if (isset($show_panel) && $show_panel)
{


$page = 'INDEX';



?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="https://docs.bde-bp.fr/images/statiques/favicon.ico" />
        <title>BDE BP - ADMIN PANEL</title>
        <!-- Bootstrap -->
        <link href="https://assets.bde-bp.fr/css/bootstrap.css" rel="stylesheet">
        <!-- Icons -->
        <script src="https://kit.fontawesome.com/1fdd8d0755.js" crossorigin="anonymous"></script>
        <!-- Edition html -->
        <script src="https://cdn.ckeditor.com/4.13.0/standard/ckeditor.js"></script>
    </head>
<body>

    <?php include('./content/nav.php'); ?>

    <div class="container mt-5 pt-5">
   
        <?php include('./content/alert_display.html'); ?>

        <!-- CHIFFRES -->
        <div class="row">
				
            <div class="col-md-4 card bg-info text-center">
                <div class="card-body p-3 h-100">
                    <div class="row align-items-center h-100">
                        <div class="col align-self-center">
                            <h3 class="font-weight-bold">Visites du jour</h3>
                            <h4 class="text-dark"><?php echo $visite_jour; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 card bg-success text-center">
                <div class="card-body p-3 h-100">
                    <div class="row align-items-center h-100">
                        <div class="col align-self-center">
                            <h3 class="font-weight-bold">Adhérents</h3>
                            <h4 class="text-dark"><?php echo $gestion_adherents->CountAdherents(); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 card bg-secondary text-center">
                <div class="card-body p-3 h-100">
                    <div class="row align-items-center h-100">
                        <div class="col align-self-center">
                            <h3 class="font-weight-bold">Articles visibles</h3>
                            <h4 class="text-dark"><?php echo $gestion_articles->CountArticlesVisibles(); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 card bg-warning text-center">
                <div class="card-body p-3 h-100">
                    <div class="row align-items-center h-100">
                        <div class="col align-self-center">
                            <h3 class="font-weight-bold">Mails journaliers restants</h3>
                            <h4 class="text-dark"><?php echo $gestion_mails->getCredits(); ?></h4>
                        </div>
                    </div>
                </div>
            </div>

                    
        </div>




<?php

if ($panel_admin->functionAllowed(panel_admin::FCN_ISREGISTEREDFOREVENT))
{

?>


        <hr class="my-5">





		<!-- DERNIERES INSCRIPTIONS -->	
        <h2>Dernières inscriptions aux évènements</h2>

<?php

$derniers_inscrits = $panel_admin->getLastInscrits();

?>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">Horodatage</th>
                    <th scope="col">Adhérent</th>
                    <th scope="col">Nom</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>

<?php

foreach($derniers_inscrits as $inscrit)
{

?>
                <tr>
                    <th scope="row"><?php echo $inscrit['TimeStamp']; ?></th>
                    <td><?php echo $inscrit['IDA']; ?></td>
                    <td><?php echo $inscrit['Nom']; ?></td>
                    <td class="text-right"><a class="btn btn-light text-dark" href="./events.php?inscription&ArticleID=<?php echo $inscrit['ArticleID']; ?>&InscriptionID=<?php echo $inscrit['InscriptionID']; ?>">Consulter</a></td>
                </tr>
<?php
}
?>


            </tbody>
        </table>

<?php
}
?>


    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) --> 
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    </body>
</html>
<?php
}
?>