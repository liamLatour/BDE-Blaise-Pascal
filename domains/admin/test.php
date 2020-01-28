<?php

include('./content/panel_base.php');

if (isset($show_panel) && $show_panel)
{
    var_dump($gestion_mails->getCredits());
}


?>