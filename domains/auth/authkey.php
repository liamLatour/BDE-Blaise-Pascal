<?php
include('../../dependances/class/base.php');

if (isset($_REQUEST['id']) && isset($_REQUEST['password']))
{
    $ask_key = $gestion_adherents->auth_key_askKey($_REQUEST['id'], $_REQUEST['password']);
    if (err::c($ask_key))
    {
        header('Content-Type: application/json');
        echo json_encode(["auth_key" => $ask_key], JSON_PRETTY_PRINT);
    }
    else
    {
        switch($ask_key->g())
        {
            case err::WRONG_PASS:
                $error = "WRONG_LOGIN";
                break;
            case err::ADMIN_FCNFORBID:
                $error = "NO_PERM";
                break;
            case err::BDD_ERROR:
                $error = "BDD_ERROR";
                break;
            default:
                $error = "BDD_ERROR";
                break;
        }
        header('Content-Type: application/json');
        echo json_encode(["error" => $error], JSON_PRETTY_PRINT);
    }
}
else if (isset($_REQUEST['key']))
{
    header('Content-Type: application/json');
    echo json_encode(["verif_key" => $gestion_adherents->auth_key_verifKey($_REQUEST['key']) !== false ], JSON_PRETTY_PRINT);
}
else
{
    header('Content-Type: application/json');
    echo json_encode(["error" => "NO_REQUEST"], JSON_PRETTY_PRINT);
}

?>