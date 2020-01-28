<?php
include('../../../dependances/class/base.php');


if (isset($_GET['i']))
{
    $image = $gestion_images->getImage($_GET['i']);

    header('Content-Type: '.$image->getType());

    echo $image->getData();
}
else if (isset($_GET['qr']) && trim($_GET['qr']) != '' && preg_match("/^([A-Za-z0-9])+$/", $_GET['qr']))
{
    include('../../../dependances/extensions/phpqrcode/qrlib.php');
    $PNG_SERV_DIR = './temp/qrcodes/';
    $filename = $PNG_SERV_DIR.$_GET['qr'].'.png'; 
    QRcode::png($_GET['qr'], $filename, 'L', 4, 0);    
    header('Content-Type: image/png');
    readfile($filename);
    unlink($filename);
}
else
{
    die();
}
?>