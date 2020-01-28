<?php

$current_page = basename($_SERVER['PHP_SELF'],'.php');

?>


<header id="header">
    <aside style=""><a href="https://bde-bp.fr" style="border-bottom: solid 0px;"><img src="https://docs.bde-bp.fr/images/statiques/logo.png" alt=""></a></aside>
    <div>
        <h1 id="logo">
            <a href="https://bde-bp.fr">BDE<span> Blaise Pascal</span></a>
        </h1>
    </div>
    <nav id="nav">
        <ul>
            <li <?php if ($current_page == 'index') { echo 'class="current"'; }?>>
                <a href="https://bde-bp.fr">Accueil</a>
            </li>
<?php include("nav.php") ?>
        </ul>
    </nav>
</header>