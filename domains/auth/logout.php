<?php

// if (isset($_GET['force']))
// {
//     session_start();
//     unset($_SESSION);
//     session_destroy();
//     header('Location: https://bde-bp.fr');
// }

include('../../dependances/class/base.php');

gestion_logs::Log($ip, log::TYPE_COMPTE, 'auth/logout', '');

// last page
if (isset($_SESSION['lastpage']))
{
    $lastpage = (string) $_SESSION['lastpage'];
}
else
{
    $lastpage = '';
}

$gestion_adherents->logout();

if ($lastpage != '')
{
    header('Location: '.$lastpage);
    unset($_SESSION['lastpage']);
}
else
{
    header('Location: https://bde-bp.fr');
}

?>