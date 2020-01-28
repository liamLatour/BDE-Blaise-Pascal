<?php

$current_page = basename($_SERVER['PHP_SELF'],'.php');

?>

<li <?php if ($current_page == 'lecture') { echo 'class="current"'; }?>>
    <a href="https://articles.bde-bp.fr/">Articles</a>
</li>
<!-- <li <?php if ($current_page == 'contact') { echo 'class="current"'; }?>>
    <a href="contact">Contact</a>
</li> -->

<li class="submenu">
    <a href="#">Plus</a>
    <ul style="max-width: 270px">
        <!-- <li>
            <a href="bureau">Photos</a>
        </li> -->
        <li <?php if ($current_page == 'bureau') { echo 'class="current"'; }?>>
            <a href="https://bde-bp.fr/bureau">Le Bureau</a>
        </li>
        <li>
            <a href="https://admin.bde-bp.fr">Pannel Admin</a>
        </li>
    </ul>
</li>

<?php
if ($gestion_adherents->isRegisteredORConnected())
{
?>
<li>
    <a href="https://adherent.bde-bp.fr" class="button primary">Mon compte</a>
</li>
<?php
}
else
{
    ?>
<li>
    <a href="https://auth.bde-bp.fr/" class="button">Connexion</a>
</li>

<?php
if (gestion_adherents::publicationAdhesion())
{
?>
<li>
    <a href="https://adherent.bde-bp.fr/adhesion" class="button primary">Adhésion</a>
</li>
<?php
}
}
?>

