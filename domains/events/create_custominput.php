<?php
include('../../dependances/class/base.php');

if (isset($_POST["create_one"]))
{
    if (    isset($_POST["Name"])
            && isset($_POST["Label"])
            && isset($_POST["Placeholder"])
            && isset($_POST["Type"])
            && isset($_POST["Obligatoire"])
            && isset($_POST["RegEx"])
            && isset($_POST["ErrorMsg"])
            && isset($_POST["Options"]))
    {
        $Name = slugify($_POST["Name"]);
        $Label = htmlspecialchars(strip_tags($_POST["Label"]));
        $Placeholder = htmlspecialchars(strip_tags($_POST["Placeholder"]));
        $ErrorMsg = htmlspecialchars(strip_tags($_POST["ErrorMsg"]));
        $Type = event_custominput::stringToType($_POST["Type"]);
        $Obligatoire = $_POST["Obligatoire"] == "true";
        if ($_POST["RegEx"] == "NULL")
        {
            $Verif = NULL;
        }
        else
        {   
            $Verif = $_POST["RegEx"];
        }
        if ($_POST["Options"] == "NULL")
        {
            $Options = NULL;
        }
        else
        {   
            $Options = event_custominput::buildOptionsArray($_POST["Options"]);
        }
       
        
        $custom_input = new event_custominput($Name, $Label, $Placeholder, $Type, $Obligatoire, $Verif, $ErrorMsg, $Options);
        echo "<pre>".htmlentities($custom_input->getInput())."</pre><hr>";
        echo $custom_input->getInput()."<hr>";

        $custom_input = $custom_input->export();

        echo '<input type="readonly" value="'.$custom_input.'" style="width: 100%;"><hr>';
    }
}
else if (isset($_POST["edit"]) && isset($_POST['CI']))
{
    $custom_input_import = event_custominput::import($_POST['CI']);
    $_SESSION['events']['create_custominput']['custom_input_import'] = $custom_input_import;
    // eyJuIjoiY2hvaXgtY2xhc3NlIiwibCI6IiZxdW90O2Nob2l4IGNsYXNzZSZxdW90OyIsInAiOiImcXVvdDsmcXVvdDsmcXVvdDsmcXVvdDtDaG9pb3gmcXVvdDsiLCJ0IjoxMDEsInIiOm51bGwsIm5kIjp0cnVlLCJvIjpudWxsLCJ2IjpudWxsfQ==
    // var_dump($custom_input_import);
}

function edit(string $champ, string $value = "")
{
    if (isset($_SESSION['events']['create_custominput']['custom_input_import']))
    {
        $custom_input_import = $_SESSION['events']['create_custominput']['custom_input_import'];
        if ($champ == "Options")
        {
            echo event_custominput::optionsToString($custom_input_import->Options);
        }
        else if ($champ == "Type")
        {
            $s = 'selected="selected" default';
            switch($custom_input_import->Type)
            {
                case event_custominput::TYPE_TEXT:
                    if ($value == "TEXT") { echo $s; } break;
                case event_custominput::TYPE_STRING:
                    if ($value == "STRING") { echo $s; } break;
                case event_custominput::TYPE_SELECT:
                    if ($value == "SELECT") { echo $s; } break;
                case event_custominput::TYPE_NUM:
                    if ($value == "NUM") { echo $s; } break;
                case event_custominput::TYPE_EMAIL:
                    if ($value == "EMAIL") { echo $s; } break;
                case event_custominput::TYPE_TEL:
                    if ($value == "TEL") { echo $s; } break;
                case event_custominput::TYPE_DATE:
                    if ($value == "DATE") { echo $s; } break;
                case event_custominput::TYPE_TIME:
                    if ($value == "TIME") { echo $s; } break;
                case event_custominput::TYPE_DT:
                    if ($value == "DT") { echo $s; } break;
                case event_custominput::TYPE_RADIO:
                    if ($value == "RADIO") { echo $s; } break;
                case event_custominput::TYPE_CHECKBOXES:
                    if ($value == "CHECKBOXES") { echo $s; } break;
            }
        }
        else if ($champ == "Obligatoire")
        {
            if ($value = "true" && $custom_input_import->Obligatoire)
            {
                echo 'checked="checked"';
            }
            else if ($value = "false" && !$custom_input_import->Obligatoire)
            {
                echo 'checked="checked"';
            }
        }
        else if ($champ == "RegEx")
        {
            if (is_null($custom_input_import->RegEx))
            {
                echo "NULL";
            }
            else
            {
                echo $custom_input_import->RegEx;
            }
        }
        else
        {
            echo $custom_input_import->$champ;
        }
    }
}



?>
<form action="" method="post">
    <label>Champ Personnalisé</label><input type="text" name="CI" required="required">
    <input type="submit" name="edit" value="Editer">
</form>
<hr>
<form action="" method="post">
    <label>Nom</label><input type="text" name="Name" required="required" value="<?php edit("Name") ?>"><br/>

    <label>Label</label><input type="text" name="Label" required="required"  value="<?php edit("Label") ?>"><br/>

    <label>Placeholder</label><input type="text" name="Placeholder" required="required"  value="<?php edit("Placeholder") ?>"><br/>

    <label for="Type">Type</label>
        <select  name="Type" required="required"  >
            <option value="" disabled="disabled"  selected="selected" default  >Type</option>
            <option <?php edit("Type", "TEXT") ?> value="TEXT"  >Paragraphe</option>
            <option <?php edit("Type", "STRING") ?> value="STRING"  >Classique</option>
            <option <?php edit("Type", "NUM") ?> value="NUM"  >Nombre</option>
            <option <?php edit("Type", "EMAIL") ?> value="EMAIL"  >Email</option>
            <option <?php edit("Type", "TEL") ?> value="TEL"  >Téléphone</option>
            <option <?php edit("Type", "DATE") ?> value="DATE"  >Date</option>
            <option <?php edit("Type", "TIME") ?> value="TIME"  >Heure</option>
            <option <?php edit("Type", "DT") ?> value="DT"  >Date + Heure</option>
            <option <?php edit("Type", "SELECT") ?> value="SELECT"  >Liste</option>
            <option <?php edit("Type", "RADIO") ?> value="RADIO"  >Cases à cocher uniques</option>
            <option <?php edit("Type", "CHECKBOXES") ?> value="CHECKBOXES"  >Cases à cocher multiples</option>
        </select></br>

    <label for="Obligatoire">Obligatoire</label></br>
    <input name="Obligatoire" type="radio"  id="Obligatoire-true" value="true" required="required" <?php edit("Obligatoire", "true") ?>><label for="Obligatoire-true" >Oui</label>
    <input name="Obligatoire" type="radio"  id="Obligatoire-false" value="false" required="required" <?php edit("Obligatoire", "false") ?>><label for="Obligatoire-false" >Non</label></br>
    <small>Les cases à cocher MULTIPLES ne peuvent pas être obligatoires.</small></br>

    <label>RegEx</label><input type="text" name="RegEx" required="required" value="<?php edit("RegEx") ?>"><br/>

    <label>ErrorMsg</label><input type="text" name="ErrorMsg" required="required" value="<?php edit("ErrorMsg") ?>"><br/>

    <label>Options</label><input type="text" name="Options" required="required" value="<?php edit("Options") ?>"><br/>
    <small>Séparer les options par des ";" </br>
    Vous devez définir des options pour les Listes et Cases à cocher</small></br>

    <input type="submit" name="create_one" value="Créer une question personnalisée">
</form>

<?php
    unset($_SESSION['events']['create_custominput']['custom_input_import']);
?>
