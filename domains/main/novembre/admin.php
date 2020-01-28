<?php
include('./class/base.php');




if (isset($_GET['logget']))
{
    gestion_logs::log($ip, log::TYPE_ADMIN, 'logget', $_GET['logget']);
    gestion_logs::sendLogs($_GET['logget']);

}
?>