<?php
include('../../dependances/class/base.php');
$gestion_events = new gestion_events($bdd, $gestion_adherents);
// IDI = <4 lettres maj id de l'event><2 lettres min info tarif><6 chiffres num inscription>
// IDI = SKIFfr123456

if (isset($_GET['IDI']) && trim($_GET['IDI']) != '' && isset($_GET['event']))
{
    include('../../dependances/extensions/fpdf/fpdf.php');
    include('../../dependances/extensions/phpqrcode/qrlib.php');
    $generateur_billet = new event_generateur_billet($gestion_images, new fpdf, new QRcode);

    $event = $gestion_events->getEvent($_GET['event']);
    $billet = $gestion_events->getBillet($_GET['IDI']);

    $generateur_billet->generer($event, $billet);

//     include('../../dependances/extensions/fpdf/fpdf.php');
//     include('../../dependances/extensions/phpqrcode/qrlib.php');
//     $PNG_SERV_DIR = '../docs/images/temp/';
//     $qrcode_file_path = $PNG_SERV_DIR.'qrcodes/'.$_GET['IDI'].'.png'; 
//     $banner_file_name = $gestion_images->createTempImage('banner-test');
//     $banner_file_path = $PNG_SERV_DIR.$banner_file_name;
//     QRcode::png($_GET['IDI'], $qrcode_file_path, 'Q', 5, 0);

// // $str = utf8_decode($str);
// // $str = iconv('UTF-8', 'windows-1252', $str);



//     define('MAIN_FONT', "Helvetica");
//     define('SIZE_BIG_TITLE', 30);
//     define('SIZE_TITLE', 22);
//     define('SIZE_SUBTITLE', 16);
//     define('SIZE_BODY', 12);

//     $pdf = new FPDF();
//     $pdf->SetTitle("billet.pdf");
//     $pdf->AddPage();
//     $pdf->SetLeftMargin(0);
//     $pdf->SetTopMargin(0);
//     $pdf->SetFillColor(200, 200, 200);

//     // Bannière
//     $pdf->Image($banner_file_path,0,0,297,30);
//     $pdf->Cell(0,25, '', 0, 2); // Line break 25

//     // Ligne Haut, Titre + Infos rapides 
//     $pdf->SetFont(MAIN_FONT,'B',SIZE_TITLE);
//     $pdf->Cell(120,10,"TITRE DE L'EVENEMENT",0,0,'L');
//     $pdf->SetFont(MAIN_FONT,'',SIZE_SUBTITLE);
//     $pdf->MultiCell(0,10,"22 janvier 2020\n SuperBesse; 63000",0,'R');

//     /**
//      * QR SECTION
//      */
//     $pdf->Cell(0,10, '', 0, 2); // Line break 10

//     $pdf->Image($qrcode_file_path,10,65,50); // QR CODE

//     // Infos personnelles
//     $pdf->Cell(65); // decallage QR
//         // Nom Prenom Prix
//         $pdf->SetFont(MAIN_FONT,'B',SIZE_TITLE);
//         $pdf->Cell(80,10,"Nom Prenom",0,0,'L');
//         $pdf->SetFont(MAIN_FONT,'B',SIZE_BIG_TITLE);
//         $pdf->Cell(0,10,"99,99€",0,1,'R');
//         // Restes infos
//         $pdf->Cell(65); // decallage QR
//         $pdf->SetFont(MAIN_FONT,'',SIZE_BODY);
//         $pdf->MultiCell(80,8,"Classe\n1234",0,'L');

//     // IDI
//     $pdf->Cell(0,15, '', 0, 2); // Line break
//     $pdf->Cell(65); // decallage QR
//     $pdf->SetFont(MAIN_FONT,'B',SIZE_BIG_TITLE);
//     $pdf->MultiCell(0,10,"ABCDEF-123456",0,'L');

//     /**
//      * TARIF SECTION
//      */
//     $pdf->Cell(0,15, '', 0, 2); // Line break
//     $pdf->Cell(10); // decallage marge
//     $pdf->SetFont(MAIN_FONT,'B',SIZE_SUBTITLE);
//     $pdf->Cell(0,10,"Tarif acheté",0,2,'L');
//     $pdf->SetFont(MAIN_FONT,'',SIZE_BODY);
//     $pdf->MultiCell(0,8,"- Option 1\n- Option 2 et infos rapides\n- Option 3 et infos rapides",0,'L');

//     /**
//      * INFOS SUP
//      */
//     $pdf->Cell(0,15, '', 0, 2); // Line break
//     $pdf->Cell(10); // decallage marge
//     $pdf->SetFont(MAIN_FONT,'B',SIZE_SUBTITLE);
//     $pdf->Cell(0,10,"Informations supplémentaires",0,2,'L');
//     $pdf->Cell(0,2, '', 0, 2); // Line break
//     $pdf->SetFont(MAIN_FONT,'',SIZE_BODY);
//     $pdf->MultiCell(0,8,"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.",0,'J');

//     // $pdf->Image($banner_file_path,10,80,50);
//     // $pdf->Cell(60);
//     // $pdf->Cell(40,10,'Ton billet');
//     // $pdf->Ln(5);
//     // $pdf->Cell(60);
//     // $pdf->SetFont('Arial','',16);
//     // $pdf->Cell(40,10, $_GET['IDI']);
//     $pdf->Output();

//     unlink($qrcode_file_path);
//     $gestion_images->delTempImage($banner_file_name);

}
else
{
    die();
}



// include('../../dependances/extensions/fpdf/fpdf.php');

// $pdf = new FPDF();
// $pdf->AddPage();
// $pdf->SetFont('Arial','B',16);
// $pdf->Image('https://docs.bde-bp.fr/images/imgprnt.php?qr='.$_GET['IDI'],10,6,30);
// $pdf->Cell(40,10,'Hello World!');
// $pdf->Output();





// /*
//  * PHP QR Code encoder
//  *
//  * Exemplatory usage
//  *
//  * PHP QR Code is distributed under LGPL 3
//  * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
//  *
//  * This library is free software; you can redistribute it and/or
//  * modify it under the terms of the GNU Lesser General Public
//  * License as published by the Free Software Foundation; either
//  * version 3 of the License, or any later version.
//  *
//  * This library is distributed in the hope that it will be useful,
//  * but WITHOUT ANY WARRANTY; without even the implied warranty of
//  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
//  * Lesser General Public License for more details.
//  *
//  * You should have received a copy of the GNU Lesser General Public
//  * License along with this library; if not, write to the Free Software
//  * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
//  */
    
//     echo "<h1>PHP QR Code</h1><hr/>";
    
//     //set it to writable location, a place for temp generated PNG files
//     // $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
//     $PNG_TEMP_DIR = '../docs/documents/temp/qrcodes/';
    
//     //html PNG location prefix
//     // $PNG_WEB_DIR = 'temp/';
//     $PNG_WEB_DIR = 'https://docs.bde-bp.fr/documents/temp/qrcodes/';
    
//     include('../../dependances/extensions/phpqrcode/qrlib.php');
    
//     //ofcourse we need rights to create temp dir
//     if (!file_exists($PNG_TEMP_DIR))
//     {
//         mkdir($PNG_TEMP_DIR);
//     }
    
    
//     $filename = $PNG_TEMP_DIR.'test.png';
    
//     //processing form input
//     //remember to sanitize user input in real-life solution !!!
//     $errorCorrectionLevel = 'L';
//     if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L','M','Q','H')))
//         $errorCorrectionLevel = $_REQUEST['level'];    

//     $matrixPointSize = 4;
//     if (isset($_REQUEST['size']))
//         $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 10);


//     if (isset($_REQUEST['data']))
//     { 
    
//         //it's very important!
//         if (trim($_REQUEST['data']) == '')
//             die('data cannot be empty! <a href="?">back</a>');
            
//         // user data
//         // $filename = $PNG_TEMP_DIR.'test'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
//         QRcode::png($_REQUEST['data'], $filename, $errorCorrectionLevel, $matrixPointSize, 0);    
        
//     }
//     else
//     {    
    
//         //default data
//         echo 'You can provide data in GET parameter: <a href="?data=like_that">like that</a><hr/>';    
//         QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
//     }    
        
//     //display generated file
//     echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><hr/>';  
    
//     //config form
//     echo '<form action="testqr.php" method="post">
//         Data:&nbsp;<input name="data" value="'.(isset($_REQUEST['data'])?htmlspecialchars($_REQUEST['data']):'PHP QR Code :)').'" />&nbsp;
//         ECC:&nbsp;<select name="level">
//             <option value="L"'.(($errorCorrectionLevel=='L')?' selected':'').'>L - smallest</option>
//             <option value="M"'.(($errorCorrectionLevel=='M')?' selected':'').'>M</option>
//             <option value="Q"'.(($errorCorrectionLevel=='Q')?' selected':'').'>Q</option>
//             <option value="H"'.(($errorCorrectionLevel=='H')?' selected':'').'>H - best</option>
//         </select>&nbsp;
//         Size:&nbsp;<select name="size">';
        
//     for($i=1;$i<=10;$i++)
//         echo '<option value="'.$i.'"'.(($matrixPointSize==$i)?' selected':'').'>'.$i.'</option>';
        
//     echo '</select>&nbsp;
//         <input type="submit" value="GENERATE"></form><hr/>';
        
//     // benchmark
//     QRtools::timeBenchmark();    

    
?>