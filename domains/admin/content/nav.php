<nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-primary">
    <a class="navbar-brand text-dark font-weight-bold" href="https://bde-bp.fr"><i class="fas fa-home"></i></a>
    <a class="navbar-brand text-dark font-weight-bold" href="index">PANEL ADMIN <span style="font-size: 0.6em;"><span class="badge badge-dark"><?php echo settings::p('panel_admin')['version']; ?></span></span> </a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#droite" aria-controls="droite" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="droite">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item <?php if ($page == 'INDEX') { echo 'active'; } ?>">
                <a class="nav-link" href="https://admin.bde-bp.fr">Accueil</a>
            </li>
            <li class="nav-item <?php if ($page == 'ADHERENTS') { echo 'active'; } ?>">
                <a class="nav-link <?php if (!$panel_admin->functionAllowed(panel_admin::FCN_VIEWADHERENTS)) { echo 'disabled'; } ?>" href="adherents">Adhérents</a>
            </li>
            <li class="nav-item <?php if ($page == 'EVENTS') { echo 'active'; } ?>">
                <a class="nav-link <?php if (!$panel_admin->functionAllowed(panel_admin::FCN_ARTICLELIST)) { echo 'disabled'; } ?>" href="events">Evenements</a>
            </li>
            <li class="nav-item <?php if ($page == 'ARTICLES') { echo 'active'; } ?>">
                <a class="nav-link <?php if (!$panel_admin->functionAllowed(panel_admin::FCN_ARTICLELIST)) { echo 'disabled'; } ?>" href="articles">Articles</a>
            </li>
            <li class="nav-item <?php if ($page == 'LOGS') { echo 'active'; } ?>">
                <a class="nav-link <?php if (!$panel_admin->functionAllowed(panel_admin::FCN_LOGS)) { echo 'disabled'; } ?>" href="historique">Historique</a>
            </li>
            <li class="nav-item <?php if ($page == 'SETTINGS') { echo 'active'; } ?>">
                <a class="nav-link <?php if (!$panel_admin->functionAllowed(panel_admin::FCN_EDITSETTINGS)) { echo 'disabled'; } ?>" href="parametres">Paramètres</a>
            </li>
            <li class="nav-item <?php if ($page == 'HELP') { echo 'active'; } ?>">
                <a class="nav-link" href="help">Aide</a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item my-auto">
                <a style="color: inherit; text-decoration: inherit;" href="https://adherent.bde-bp.fr"><?php echo $admin_adherent->getPNom(); ?> <span class="badge badge-pill badge-warning"><?php echo $admin_adherent->getRoleStringShort(); ?></span></a>
            </li>
            <li class="nav-item">
                <a class="btn btn-dark ml-lg-3" href="https://auth.bde-bp.fr/logout">Se déconnecter</a>
            </li>
        </ul>
    </div>
    
</nav>
