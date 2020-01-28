<?php

include('./content/panel_base.php');

if (isset($show_panel) && $show_panel && $panel_admin->functionAllowed(panel_admin::FCN_LOGS))
{


$page = 'LOGS';


if (isset($_GET['export']) && $_GET['export'] != '')
{

    if (gestion_logs::sendLogs($_GET['export']) === false)
    {
        $erreur = "Nous n'avons pas trouvé de fichier historique à cette date.";
    }
    else
    {
        gestion_logs::Log($ip, log::TYPE_ADMIN, 'historique/export', $_GET['export']);
        die();
    }
}




?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="https://docs.bde-bp.fr/images/statiques/favicon.ico" />
        <title>BDE BP - ADMIN PANEL - HISTORIQUE</title>
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


        <h2>Récupérer un fichier historique</h2>


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
		  
		<form class="row" method="get" action="?">
			
          	<div class="form-group col-md-6">
            	<label for="export">Date</label>
            	<input type="date" class="form-control" name="export" id="export" required>
          	</div>
			
			<div class="form-group col-md mt-auto pt-2">
				<button type="submit" class="btn btn-success btn-block">Récuperer</button>
			</div>
			
			
			
        </form>
		
		
		
		
		<hr class="my-3">
		
		
		
		
		
		
		
		<div>

			<h2>Historique</h2>

<?php
$LogsFiles = gestion_logs::getAllFiles();
array_shift($LogsFiles);
array_shift($LogsFiles);


if (sizeof($LogsFiles) == 0)
{
?>

			<div class="alert alert-secondary">
				Il n'y a pas d'historique à afficher.
            </div>
            
<?php
}
else
{
?>

			<table class="table table-striped">
				<thead>
					<tr>
						<th scope="col">Jour</th>
						<th scope="col">Nb de lignes</th>
						<th scope="col">Taille</th>
						<th scope="col"></th>
					</tr>
                </thead>
                <tbody>
                
<?php

    $LogsFiles = array_reverse($LogsFiles);
    foreach ($LogsFiles as $index => $file)
    {
        $lines = gestion_logs::countLines($file);
        $size = gestion_logs::getSize($file);
?>

					<tr>
						<th scope="row"><?php echo $file; ?></th>
						<td><?php echo $lines; ?></td>
						<td><?php echo $size; ?></td>
						<td class="p-1 text-right">
							<a class="btn btn-primary" href="?export=<?php echo $file; ?>">Exporter</a>
							<!-- <a class="btn btn-outline-danger text-danger">Supprimer</a> -->
						</td>
                    </tr>
                    
<?php
    }
?>

				</tbody>
            </table>
            
<?php
}
?>
			
			<!-- <nav>
			  <ul class="pagination justify-content-center">
				<li class="page-item"><a class="page-link" href="#">Previous</a></li>
				<li class="page-item active"><a class="page-link" href="#">1</a></li>
				<li class="page-item"><a class="page-link" href="#">2</a></li>
				<li class="page-item"><a class="page-link" href="#">3</a></li>
				<li class="page-item"><a class="page-link" href="#">Next</a></li>
			  </ul>
			</nav> -->

		</div>	


    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) --> 
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    </body>
</html>
<?php
}
else
{
    echo "Le panel ne peut pas s'afficher, peut être que vous n'avez pas la permission d'être ici.";
}
?>
