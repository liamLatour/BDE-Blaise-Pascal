<?php

include('../../dependances/class/base.php');


if ($gestion_adherents->isConnected())
{
    header('Location: https://adherent.bde-bp.fr');
}


if (isset($_GET['lastpage']))
{
    $lastpage = urldecode((string) $_GET['lastpage']);
}



if (isset($_GET['IDA']) && isset($_GET['key']) && $gestion_adherents->isAdherent($_GET['IDA']))
{
    if (isset($_GET['resetpass']) && isset($_GET['key']))
    {
        if ($gestion_adherents->verifKey($_GET['IDA'], $_GET['key'], 15))
        {
            $gestion_adherents->resetPass($_GET['IDA']);
            MessagePage("Votre mot de passe a été réinitialisé, vous pouvez vous en créer un nouveau <a href=\"https://auth.bde-bp.fr?firstlogin\">ici</a>.");
            die();
        }
    }
    else if (isset($_GET['confirmpass']) && isset($_GET['key']))
    {
        $mail_auth = $gestion_adherents->verifKey((int) $_GET['IDA'], $_GET['key'], 15);
        if (!($mail_auth === false))
        {
            if($gestion_adherents->firstLogin($_GET['IDA'], '', $mail_auth['Password'], true))
            {
                gestion_logs::Log($_SESSION['IP'], log::TYPE_COMPTE, 'auth/login-firstlogged', $_SESSION);
                MessagePage("Votre mot de passe a été défini, vous pouvez vous <a href=\"https://auth.bde-bp.fr\">connecter</a>.");
                die();
            }
        }
    }
    else
    {
        $mail_auth = $gestion_adherents->verifKey((int) $_GET['IDA'], $_GET['key']);
        if (!($mail_auth === false) && $mail_auth['Password'] == NULL)
        {
            $gestion_adherents->login($_GET['IDA'], '', false);
            if (isset($_GET['cookie']))
            {
                $gestion_adherents->createConnectionCookie($_GET['IDA']);
            }
            gestion_logs::Log($_SESSION['IP'], log::TYPE_COMPTE, 'auth/login-logged', $_SESSION);
        }
    }
}

if ($lastpage != '')
{
    header('Location: '.$lastpage);
}
else
{
    header('Location: https://adherent.bde-bp.fr');
}

?>