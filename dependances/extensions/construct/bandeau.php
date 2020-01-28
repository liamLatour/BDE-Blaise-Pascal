<?php

$show_bandeau = false;

if (!$gestion_adherents->checkPaiement() && $gestion_adherents->isRegisteredORConnected())
{
    $show_bandeau = true;

    $status = $_SESSION['Adherent']->getStatus();
}


?>


<?php

if($show_bandeau)
{
?>

<section class="wrapper style2 special-alt" style="margin-bottom: 0;padding: 20px 10px 5px 10px;position: fixed;z-index: 99999;width: 100%;bottom: 0 !important;">

    <?php
    if ($status == adherent::STATUS_INSCRIT)
    {
    ?>
        <p><strong>ATTENTION:</strong> Vous avez jusqu'au <?php echo settings::p('adhesion')['date_paiement']; ?> pour payer votre adhésion, après cette date votre adhesion sera annulée.
        Vous pouvez payer par internet <a style="color: #FFF; font-weight: bold;" href="<?php echo settings::p('adhesion')['lien_helloasso']; ?>">ici</a>, ou par chèque.</p>
    <?php
    }
    else
    {
    ?>
        <p><strong>ATTENTION:</strong> Votre adhésion a été annulée, si c'est une erreur contactez un membre du bureau.</p>
    <?php 
    }
    ?>








</section>
<?php 
}
?>
