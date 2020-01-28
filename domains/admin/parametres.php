<?php

include('./content/panel_base.php');

if (isset($show_panel) && $show_panel && $panel_admin->functionAllowed(panel_admin::FCN_EDITSETTINGS))
{


$page = 'SETTINGS';

$show = 'ALL';


// Affichage du menu d'édition
if (isset($_GET['edit']))
{
    if (isset($_GET['name']))
    {
        $show = 'EDIT';

        $name = $_GET['name'];
        $value = json_encode(settings::p($_GET['name']), JSON_PRETTY_PRINT);
    }
}

// Verification puis affichage de la confirmation
else if (isset($_POST['edit']) && isset($_POST['value']))
{
    if (err::c($panel_admin->verifSetting($_POST['edit'], $_POST['value'])))
    {
        $show = 'EDIT_CONFIRM';

        $modif = $_SESSION['panel_admin']['settings']['edit'];
    }
    else
    {
        $show = 'EDIT';

        $name = $_POST['edit'];
        $value = $_POST['value'];

        $erreur = "Le format JSON n'est pas correct. Vous pouvez le vérifier via <a href=\"https://jsonformatter.curiousconcept.com/\"  target=\"_blank\">ce site</a>";
    }
}

// Annulation et affichage des valeurs précédentes
else if (isset($_GET['cancel']) && isset($_SESSION['panel_admin']['settings']['edit']))
{
    $show = 'EDIT';

    $name = $_SESSION['panel_admin']['settings']['edit']['name'];
    $value =  json_encode($_SESSION['panel_admin']['settings']['edit']['value'], JSON_PRETTY_PRINT);
}

// Confirmation et modification de la valeur
else if (isset($_GET['confirm']) && $panel_admin->verifToken($_GET['confirm']))
{
    $name = $_SESSION['panel_admin']['settings']['edit']['name'];
    $value =  $_SESSION['panel_admin']['settings']['edit']['value'];

    $edit = $panel_admin->editSetting($name, $value);
    if (err::c($edit))
    {          
        $confirm = 'Vous avez modifier le paramète <code>'.$name.'</code><br>Vous pouvez vérifer sa valeur dans le tableau ci-dessous.';
    }
    else
    {
        if ($edit->g() == err::ADMIN_JSONWRITE)
        {
            $erreur = "Une erreur lors de l'enregistrement des paramètres est survenue.";
        }
        else if ($edit->g() == err::ADMIN_BADJSON)
        {
            $erreur = "Le format JSON n'est pas correct. Vous pouvez le vérifier via <a href=\"https://jsonformatter.curiousconcept.com/\"  target=\"_blank\">ce site</a>";
        }
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
        <title>BDE BP - ADMIN PANEL - PARAMETRES</title>
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

        <div>

<?php
if ($show == 'ALL')
{
?>
            
            <?php
            if (isset($confirm))
            {
            ?>
                <div class="alert alert-success">
                    <?php echo $confirm; ?>
                </div>
            <?php
            }
            ?>
            
            
            <h2>Paramètres</h2>

            <div class="alert bg-warning">
                <strong>Attention:</strong> Soyez très prudent en modifiant les valeurs des paramètres ci-dessous.<br>
                Le code a pleine confiance en ceux-ci et faire une erreur en modifiant par exemple le type d'un paramètre ou
                les valeurs des clées d'un tableau associatif (tableau avec clées personnalisée) pourrait faire bugger complètement
                le site et afficher des erreurs visibles par tous.<br>
                <br>
                En cas de doutes consultez la section <u>AIDE</u> ou votre/vos gentil(s) WebMaster(s) :) <br>
            </div>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Nom</th>
                        <th scope="col">Valeur</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>

                <?php
                $AllSettings = settings::getAll();
                foreach($AllSettings as $set_name => $set_value)
                {
                ?>

                    <tr>
                        <th scope="row"><?php echo $set_name; ?></th>
                        <td>
                            <pre><code><?php var_dump($set_value); ?></code></pre>
                        </td>
                        <td class="p-1 text-right">
                            <a class="btn btn-info" href="?edit&name=<?php echo $set_name; ?>">Editer</a>
                        </td>
                    </tr>

                <?php
                }
                ?>

                </tbody>
            </table>

        </div>

<?php
}
else if ($show == 'EDIT')
{
?>

        <div>
            
            <h2>Edition</h2>
                
            <div class="alert alert-info">
                Vous éditez <u><strong><?php echo $name; ?></strong></u>
            </div>
                
            <div class="row mb-3">
                <div class="col-md-10"></div>
                <div class="col-md-2">
                    <a class="btn btn-secondary btn-block" href="?">Retour</a>
                </div>
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

            <div class="alert alert-warning">
                <strong>Attention: </strong> La modification doit se faire en format JSON et respecter le formattage du paramètre actuel.<br>
                Si le paramètre est un boolean, vous devez entrer un boolean.<br>
                Si c'est un tableau, vous devez entrer un tableau.<br>
                Si c'est un tableau associatif (avec des clées qui ne sont pas des entiers), vous <u>NE DEVEZ PAS</u> modifier les valeurs des clées.<br>
                Si c'est une date (JJ-MM-AAAA HH:MM:SS) vous devez respecter le format et entrer une date <u>VALIDE</u>.<br>
                <br>
                Le code a pleine confiance en ces paramètres, si vous modifier sans faire attention en modifiant par exemple le type du paramètre (entier en string, bool en entier, ...)
                vous risquer de faire bugger le site en affichant des erreurs qui seront visibles par tous.<br>

            </div>
            
            <form method="post" action="?">
                <div class="form-group">
                    <label for="value"><?php echo $name; ?></label>
                    <textarea class="form-control" id="value" name="value" rows="<?php 
                    
                    $rows = substr_count( $value, "\n" ) + 1;

                    if ($rows > 50)
                    { 
                        echo 50;
                    }
                    else
                    {
                        echo $rows;
                    }
                    
                    
                    ?>"><?php echo $value; ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success btn-block" name="edit" value="<?php echo $name; ?>">Mettre à jour</button>
                    </div>
                </div>
            </form>
            
        </div>

<?php
}
else if ($show == 'EDIT_CONFIRM')
{
?>
        <div>
            
           <h2>Edition</h2>
                
            <div class="alert alert-info">
                Vous éditez <u><strong><?php echo $modif['name']; ?></strong></u>
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
            
            <div class="border border-success py-5 px-2 mb-3">

                <div class="container">

                    <h1 class="display-4 font-weight-bold text-success">Vos modifications</h1>
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th scope="row">Vos modification pour le paramètre: <?php echo $modif['name']; ?></th>
                            </tr>
                            <tr>
                                <td>
                                    <pre><code><?php var_dump($modif['value']); ?></code></pre>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Valeur actuellement enregistrée</th>
                            </tr>
                            <tr>
                                <td>
                                    <pre><code><?php var_dump(settings::p($modif['name'])); ?></code></pre>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- <div class="alert bg-secondary">
                        <h6>Valeur originale</h6>
                        <pre><code class="text-dark"><?php 
                        //var_dump(settings::p($modif['name'])); ?></code></pre>
                    </div> -->
                    
                    <div class="btn-group">
                        <a class="btn btn-success" href="?confirm=<?php echo $panel_admin->getToken(); ?>">Confirmer ces modification</a>
                        <a class="btn btn-danger" href="?cancel">Annuler</a>
                    </div>
                    
                </div>

            </div>

        </div>

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
else
{
    echo "Le panel ne peut pas s'afficher, peut être que vous n'avez pas la permission d'être ici.";
}
?>