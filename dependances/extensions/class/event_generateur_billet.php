<?php


class event_generateur_billet
{

    const   MAIN_FONT       = "Helvetica",
            SIZE_BIG_TITLE  = 30,
            SIZE_TITLE      = 22,
            SIZE_SUBTITLE   = 16,
            SIZE_BODY       = 12,

            PNG_SERV_DIR = '../../domains/docs/images/temp/';

    private $_gestion_images,
            $_fpdf,
            $_QRcode;

    public function __construct($gestion_images, $fpdf, $QRcode)
    {
        $this->_gestion_images = $gestion_images;
        $this->_fpdf = $fpdf;
        $this->_QRcode = $QRcode;
    }


    public function generer(event $event, event_billet  $billet)
    {
        $qrcode_file_path = self::PNG_SERV_DIR.'qrcodes/'.$billet->ID.'.png'; 

        $event_main_infos = $event->getMainInfos();

        $banner_file_name = $this->_gestion_images->createTempImage($event_main_infos['Banner_slug']);
        $banner_file_path = self::PNG_SERV_DIR.$banner_file_name;

        $this->_QRcode->png($billet->ID, $qrcode_file_path, 'Q', 5, 0);
        
        $pdf = $this->_fpdf;
        $pdf->SetTitle("billet_".$event_main_infos['Slug']."_".$billet->ID.".pdf");
        $pdf->AddPage();
        $pdf->SetLeftMargin(0);
        $pdf->SetTopMargin(0);
        $pdf->SetFillColor(200, 200, 200);

        // Bannière
        $pdf->Image($banner_file_path,0,0,210,30);
        $pdf->Cell(0,25, '', 0, 2); // Line break 25

        // Ligne Haut, Titre + Infos rapides 
        $pdf->SetFont(self::MAIN_FONT,'B',self::SIZE_TITLE);
        $pdf->Cell(120,10,$event_main_infos['Titre'],0,0,'L');
        $pdf->SetFont(self::MAIN_FONT,'',self::SIZE_SUBTITLE);
        $pdf->MultiCell(0,10,$event_main_infos['Soustitre'],0,'R');

        /**
         * QR SECTION
         */
        $pdf->Cell(0,10, '', 0, 2); // Line break 10

        $pdf->Image($qrcode_file_path,10,55,50); // QR CODE

        // Infos personnelles
        $pdf->Cell(65); // decallage QR
            // Nom Prenom Prix
            $pdf->SetFont(self::MAIN_FONT,'B',self::SIZE_TITLE);
            $pdf->Cell(80,10,utf8_decode(strtoupper($billet->Nom))." ".utf8_decode($billet->Prenom),0,0,'L');
            $pdf->SetFont(self::MAIN_FONT,'B',self::SIZE_BIG_TITLE);
            $pdf->Cell(0,10,utf8_decode($billet->tarif_Prix).iconv('UTF-8', 'windows-1252', "€"),0,1,'R');
            // Restes infos
            $pdf->Cell(65); // decallage QR
            $pdf->SetFont(self::MAIN_FONT,'',self::SIZE_BODY);
            $pdf->MultiCell(80,8,utf8_decode($billet->Classe)."\nIdentifiant: ".utf8_decode($billet->ID_adherent),0,'L');

        // IDI
        $pdf->Cell(0,15, '', 0, 2); // Line break
        $pdf->Cell(65); // decallage QR
        $pdf->SetFont(self::MAIN_FONT,'B',self::SIZE_BIG_TITLE);
        $pdf->MultiCell(0,10,utf8_decode($billet->ID),0,'L');

        /**
         * TARIF SECTION
         */
        $pdf->Cell(0,15, '', 0, 2); // Line break
        $pdf->Cell(10); // decallage marge
        $pdf->SetFont(self::MAIN_FONT,'',self::SIZE_BODY);
        $pdf->MultiCell(0,8,utf8_decode($billet->get_CustomForm_event_String($event)),0,'L');
        $pdf->Cell(10); // decallage marge
        $pdf->SetFont(self::MAIN_FONT,'B',self::SIZE_SUBTITLE);
        $pdf->Cell(0,10,utf8_decode($billet->tarif_Nom),0,2,'L');
        $pdf->Cell(10); // decallage marge
        $pdf->SetFont(self::MAIN_FONT,'',self::SIZE_BODY);
        $pdf->MultiCell(0,8,utf8_decode($billet->get_CustomForm_Tarif_String($event)),0,'L');

        /**
         * INFOS SUP
         */
        $pdf->Cell(0,15, '', 0, 2); // Line break
        $pdf->Cell(10); // decallage marge
        $pdf->SetFont(self::MAIN_FONT,'B',self::SIZE_SUBTITLE);
        $pdf->Cell(0,10,utf8_decode("Informations supplémentaires"),0,2,'L');
        $pdf->Cell(0,2, '', 0, 2); // Line break
        $pdf->SetFont(self::MAIN_FONT,'',self::SIZE_BODY);
        $pdf->MultiCell(0,8,$event_main_infos['Description'],0,'J');

        $pdf->Output();

        unlink($qrcode_file_path);
        $gestion_images->delTempImage($banner_file_name);
    }
}


?>