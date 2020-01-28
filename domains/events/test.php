<?php
include('../../dependances/class/base.php');
if (isset($_POST['slugify']) && isset($_POST['name']))
{
    var_dump(slugify($_POST['name']));
}

if (isset($_POST['custominput']))
{
    var_dump($_POST);
}
?>


<!-- <img src="https://docs.bde-bp.fr/images/imgprnt.php?i=test" style="width: 250px" alt="<?php echo $alt ?>"> -->

<form action="test.php" method="post">
    <input type="text" name="name">
    <input type="submit" name="slugify" value="Slugify">
</form>

<hr>
<form method="post">
<?php
$name = new event_custominput("ask-name", "Votre Nom", "Dupont", event_custominput::TYPE_STRING, true);
echo $name->getInput();
echo '</br>';

$options = event_custominput::buildOptionsArray("MPSI1;MPSI2;PSI");
$classe = new event_custominput("ask-classe", "Votre Classe", "Classe", event_custominput::TYPE_SELECT, true, NULL, $options);
echo $classe->getInput();
echo '</br>';

$message = new event_custominput("ask-message", "Votre Message", "Ecrivez du texte", event_custominput::TYPE_TEXT, true);
echo $message->getInput();
echo '</br>';

$classes_radio = new event_custominput("ask-classe-radio", "Votre Choisissez une classe", "La radio", event_custominput::TYPE_RADIO, true, NULL, $options);
echo $classes_radio->getInput();
echo '</br>';

$classes_cb = new event_custominput("ask-classe-cb", "Votre Choisissez une classe", "La radio", event_custominput::TYPE_CHECKBOXES, true, NULL, $options);
echo $classes_cb->getInput();
?>
<input type="submit" name="custominput" value="send">
</form>