<?php
include('./class/base.php');

error_reporting( E_ALL ^ E_NOTICE );
@set_error_handler("__error_handler" );
function __error_handler($errno, $errstr, $errfile, $errline){
   //echo "<br/>ERREUR/WARNING : $errno, $errstr, $errfile, $errline<br/>";// à décommenté si tu veux les erreurs dans la page
   trace("ERREUR/WARNING: $errno, $errstr, $errfile, $errline" );
}
function trace($toTrace){
   $f = fopen("trace.txt", "a+" );
   fwrite($f, $toTrace."\n" );
   fclose($f);
}



$show = "1classe";

$actif = (bool) settings::p('pulls')['actif'];

$today = new DateTime();
$datedebut = new DateTime((string) settings::p('pulls')['date_debut']);
$datefin  = new DateTime((string) settings::p('pulls')['date_fin']);

if ($today->getTimestamp() >= $datedebut->getTimestamp() && $today->getTimestamp() <= $datefin->getTimestamp())
{
    $datecheck = true;
}
else
{
    $datecheck = false;
}


if(isset($_GET['cancel']))
{
  unset($_SESSION['pulls']); 
  header('Location: https://bde-bp.fr');
}
elseif(isset($_GET['prev']))
{
    if (isset($_SESSION['pulls']['step']))
    {
        switch ($_SESSION['pulls']['step'])
        {
            case $_SESSION['pulls']['step'] == '2multi':

            $show = "1classe";
            break;



            case $_SESSION['pulls']['step'] == '3adh':

            $show = "2multi";
            break;


            case $_SESSION['pulls']['step'] == '4form':

            $show = "3adh";
            break;


            case $_SESSION['pulls']['step'] == '5verif':

            $show = "4form";
            break;


            case $_SESSION['pulls']['step'] == 'showc':

              $show = "getc";
              break;
        }
    }
}
elseif (isset($_GET['next']))
{
    switch ($_GET['next'])
    {
        
      
      
        case $_GET['next'] == '1classe':

        if (isset($_GET['Classe']) && array_key_exists($_GET['Classe'], settings::p('pulls_pulls')))
        {
            $_SESSION['pulls']['post']['Classe'] = $_GET['Classe'];
            $show = '2multi';
        }
        break;








        case $_GET['next'] == '2multi':

        if (isset($_SESSION['pulls']['post']['Classe']))
        {
            $show = '3adh';
        }
        break;







        case $_GET['next'] == '3adh':

        if (isset($_SESSION['pulls']['post']['Classe']))
        {
            if (isset($_GET['IDA']))
            {
                if ($gestion_adherents->getAdherent((int) $_GET['IDA']))
                {
                  if (uniIDA((int) $_GET['IDA'], $bdd))
                  {
                    $_SESSION['pulls']['post']['IDA'] = $_GET['IDA'];
                    $show = '4form';
                  }
                  else
                  {
                      $erreur = "Cet identifiant a déjà été utilisé pour effectuer une commande.<br>
                      Si vous voulez faire plusieurs commande, vous ne pourrez vous identifier que pour une seule d'entre elles.<br>
                      <br>
                      Ne vous identifiez pas si vous avez déjà commandé avec cet identifiant.";
                      $show = '3adh';
                  }

                }
                else
                {
                    $erreur = "Ce numéro d'adhérent ne correspond à aucun adhérent enregistré.";
                    $show = '3adh';
                }
            }
            else
            {
                $_SESSION['pulls']['post']['IDA'] = '';
                $show = '4form';
            }
        }
        break;








        case $_GET['next'] == '4form':

        if (isset($_SESSION['pulls']['post']['Classe']) && isset($_SESSION['pulls']['post']['IDA']))
        {
            if (isset($_GET['Nom']) && isset($_GET['Prenom']) && isset($_GET['Taille']) && isset($_GET['Email']))
            {
                $_SESSION['pulls']['post']['Infos']['Nom'] = ucwords($_GET['Nom']);
                $_SESSION['pulls']['post']['Infos']['Prenom'] = ucwords($_GET['Prenom']);
                $_SESSION['pulls']['post']['Infos']['Taille'] = $_GET['Taille'];
                $_SESSION['pulls']['post']['Infos']['Email'] = $_GET['Email'];
                if (isset($_GET['Surnom']) && $_GET['Surnom'] != '' && ChampSurnom())
                {
                    $_SESSION['pulls']['post']['Infos']['Surnom'] = $_GET['Surnom'];
                }
                else
                {
                  $_SESSION['pulls']['post']['Infos']['Surnom'] = NULL;
                }

                if ($gestion_adherents->verifyPNom($_GET['Nom']) && $gestion_adherents->verifyPNom($_GET['Prenom']))
                {
                  if ($gestion_adherents->verifyEmailForm($_GET['Email']))
                  {
                    $_SESSION['pulls']['post']['Infos']['Verif'] = true;
                    $show = '5verif';
                  }
                  else
                  {
                    $_SESSION['pulls']['post']['Infos']['Verif'] = false;
                    $erreur = "Le format de l'adresse mail n'est pas correct.";
                    $show = '4form';
                  }
                }
                else
                {
                  $_SESSION['pulls']['post']['Infos']['Verif'] = false;
                  $erreur = "Le Nom et le Prénom doivent faire entre 3 et 30 caractères, et ne contenir que des lettres, tirets, espaces, et ces caractères : àéèëêïîç.";
                  $show = '4form';
                }
            }
            else
            {
                $erreur = "Les champs Nom, Prénom, Email et Taille sont obligatoires.";
                $show = '4form';
            }
        }
        
        break;






        case $_GET['next'] == '5verif':

        if (isset($_SESSION['pulls']['post']['Classe']) && isset($_SESSION['pulls']['post']['IDA']) && isset($_SESSION['pulls']['post']['Infos']) && $_SESSION['pulls']['post']['Infos']['Verif'])
        {
          if (!ChampSurnom())
          {
            $Surnom = NULL;
          }
          else
          {
            $Surnom = $_SESSION['pulls']['post']['Infos']['Surnom'];
          }
          $Nom = $_SESSION['pulls']['post']['Infos']['Nom'];
          $Prenom = $_SESSION['pulls']['post']['Infos']['Prenom'];
          $Taille = $_SESSION['pulls']['post']['Infos']['Taille'];
          if ($_SESSION['pulls']['post']['IDA'] != '')
          {
            $IDA = $_SESSION['pulls']['post']['IDA'];
          }
          else
          {
            $IDA = NULL;
          }
          $Classe = $_SESSION['pulls']['post']['Classe'];
          $IDC = createIDC($bdd);

          $req = $bdd->registre()->prepare("INSERT INTO pulls (Classe, Nom, Prenom, IDA, Taille, Surnom, IDC) VALUES(:Classe, :Nom, :Prenom, :IDA, :Taille, :Surnom, :IDC)");
          $res = $req->execute([
              'Nom' => $Nom,
              'Prenom' => $Prenom,
              'IDA' => $IDA,
              'Classe' => $Classe,
              'Taille' => $Taille,
              'Surnom' => $Surnom,
              'IDC' => $IDC
          ]);

          if ($res)
          {

            if ($IDA == NULL)
            {
              $prix = getPrixInt($Classe)['nadh']."€".getPrixDecimal($Classe);
              $lien = settings::p('pulls_pulls')[$Classe]['Lien'];
            }
            else
            {
              $prix = getPrixInt($Classe)['adh']."€".getPrixDecimal($Classe);
              $lien = settings::p('pulls_pulls')[$Classe]['Lien_adh'];
            }

            email($_SESSION['pulls']['post']['Infos']['Email'], $Classe, $IDC, $lien, $Taille, $prix, $Prenom, $Nom);


            $inscription_infos = [
              'Nom' => $Nom,
              'Prenom' => $Prenom,
              'Classe' => $Classe,
              'IDA' => $IDA,
              'Taille' => $Taille,
              'Surnom' => $Surnom
            ];
            

            unset($_SESSION['pulls']['post']);
            gestion_logs::Log($_SESSION['IP'], log::TYPE_EVENT, 'pulls-commande', serialize($inscription_infos));
            $show = '6confirm';

          }
          else
          {
            gestion_logs::Log($_SESSION['IP'], log::TYPE_ERROR, 'pulls', 'BDD_COMMANDE');
            $erreur = 'Une erreur de base de données est survenue, si elle perciste contactez un administrateur.';
            $show = '5verif';
          }

        }
    }
}
elseif (isset($_GET['getc']))
{
  if ($_GET['getc'] == 'nom')
  {
    if (isset($_GET['Nom']))
    {
      $req = $bdd->registre()->prepare('SELECT * FROM pulls WHERE Nom = :Nom');
      $req->execute([
          'Nom' => $_GET['Nom']
      ]);
      $req = $req->fetchAll(PDO::FETCH_ASSOC);

      if ($req)
      {
        $commandes = $req;
        gestion_logs::Log($_SESSION['IP'], log::TYPE_EVENT, 'pulls-getc', $_GET['Nom']);
        $show = 'showc';
      }
      else
      {
        $erreur = 'Aucune commande pour ce nom n\'a été trouvée.';
        $show = 'getc';
      }
    }
    else
    {
      $erreur = 'Le Nom est obligatoire.';
      $show = 'getc';
    }
  }
  elseif ($_GET['getc'] == 'idc')
  {
    if (isset($_GET['IDC']))
    {
      $req = $bdd->registre()->prepare('SELECT * FROM pulls WHERE IDC = :IDC');
      $req->execute([
          'IDC' => $_GET['IDC']
      ]);
      $req = $req->fetchAll(PDO::FETCH_ASSOC);


      if ($req)
      {
        $commandes = $req;
        gestion_logs::Log($_SESSION['IP'], log::TYPE_EVENT, 'pulls-getc', $_GET['IDC']);
        $show = 'showc';
      }
      else
      {
        $erreur = 'Ce numéro ne correspond à aucune commande.';
        $show = 'getc';
      }
    }
    else
    {
      $erreur = 'Le Numéro de Commande est obligatoire.';
      $show = 'getc';
    }
  }
  else
  {
    $show = 'getc';
  }
}


function createIDC($bdd)
{
 $IDC = random_int(123456, 987654);

 $i = 0;
 while (!uniIDC($IDC, $bdd) && $i <= 100)
 {
  if ($i != 100)
  {
    $IDC = random_int(123456, 987654);
    $i++;
  }
  else 
  {
    $IDC = 0;
  }
 }
 return $IDC;
}

function getPrixInt($classe)
{
  $prix = settings::p('pulls_pulls')[$classe]['Prix'];
  $entier = floor($prix);
  $entier_adh = $entier - 5;

  return ['nadh' => $entier, 'adh' => $entier_adh];;
}

function getPrixDecimal($classe)
{
  $prix = settings::p('pulls_pulls')[$classe]['Prix'];
  $entier = floor($prix);

  $decimal = ($prix - $entier)*100;
  if ($decimal == 0)
  {
    return "";
  }
  else
  {
    return $decimal;
  }

}

function ChampSurnom()
{
  return isset($_SESSION['pulls']['post']['Classe']) && settings::p('pulls_pulls')[$_SESSION['pulls']['post']['Classe']]['Surnom'];
}

// Renvoi true si unique, false sinon
function uniIDA(int $IDA, bdd $bdd)
{
    $req = $bdd->registre()->prepare('SELECT * FROM pulls WHERE IDA = :IDA');
    $req->execute([
        'IDA' => $IDA
    ]);
    $req = $req->fetch(PDO::FETCH_ASSOC);

    return !(bool) $req;
}

// Renvoi true si unique, false sinon
function uniIDC(int $IDC, bdd $bdd)
{
    $req = $bdd->registre()->prepare('SELECT * FROM pulls WHERE IDC = :IDC');
    $req->execute([
        'IDC' => $IDC
    ]);
    $req = $req->fetch(PDO::FETCH_ASSOC);

    return !(bool) $req;
}


$_SESSION['pulls']['step'] = $show;













function email($email, $classe, $idc, $lien, $taille, $prix, $prenom, $nom)
{
//mail envoyé par sendinblue

         include './class/Mailin.php';
         $mailin = new Mailin('contact@bde-bp.fr', 'KGyYnRvNtcqFMDmg');

        $message_html = "
        <html>
         <head>
          <title>Validation Commande Pull Numéro $idc BDE Blaise Pascal</title>
         </head>
         <body>
          <p>Cette email te confirme la bonne récétpion de ta commande $idc pour ton pull de $classe en taille $taille</p>
          <p>Maintenant que as finalisée ton inscription, il ne te reste plus qu'à régler ta commande dans les <strong>7 jours</strong> suivants cette inscription. Le montant s'élève à $prix. Tu peux les régler de deux manières :</p>
          <ul class='inner' style='padding:0em 0 2em 0; margin-bottom:0px;'>
            <li><strong>Par chèque</strong> : un chèque d'un montant de $prix à l'ordre du <span font-style:'italic'>BDE de Blaise Pascal</span> à déposer dans la boîte aux lettres de l'association, située juste à coté du hall d'accueil, en bas de l'escalier interdit aux élèves qui mène au bureau de la proviseure (si tu ne la trouves pas, demande à l'accueil de te montrer son emplacement, plus d'info <a href='https://www.bde-bp.fr/lecture/lecture?type=article&id=3'>ici</a>). Renseigne <strong>obligatoirement</strong> ton numéro de commande $idc au dos du chèque, dans le coin inférieur droit.</li>
            <li><strong>Par carte bancaire</strong> : il suffit de remplir le formulaire <a href='$lien'>HelloAsso</a>.
              HelloAsso est une plateforme gratuite  qui permet aux associations de récolter des fonds sans frais (comme PayPal, mais gratuit et réservé aux associations). L'organisme vis grâce aux pourboires, et un pourboire de 2,50€ sera automatiquement sélectionné. Il n'y a cependant
              aucune obligation de donner un quelconque pourboire ! Tous dons effectués sous l'appellation pourboire ne nous sera aucunement reversés ! Nous tenons à préciser que toutes tes informations de paiement resteront confidentielles et ne nous serons jamais communiquées.</li>
          </ul>
         </body>
        </html>";

        $mailin->
          addTo($email, $prenom." ".$nom)->
          setFrom('contact@bde-bp.fr', 'BDE Blaise Pascal')->
          setReplyTo('contact@bde-bp.fr', 'BDE Blaise Pascal')->
          setSubject("Confirmation de ta commande $idc")->
          setHtml($message_html)->
          setText("Ta commande a bien été enregistrée. Merci de la régler au plus vite soit par chèque à déposer dans la boite aux lettres du BDE (plus d'info : https://www.bde-bp.fr/lecture/lecture?type=article&id=3) soit via la plateforme de paiement en ligne sécurisée HelloAsso au lien suivant : $lien ");
        $res = $mailin->send();
}




?>




<!doctype html>
<html lang="en" style="width: 100%; height: 100%">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="./css/bootstrap.css">

    <title>BDE BP - COMMANDE PULLS DE CLASSE</title>
    <link rel="shortcut icon" href="../images/favicon.ico" />
  </head>
    <body class="h-100 w-100">
        
    <span class="badge badge-dark">FAIT AVEC AMOUR PAR PAUL ET THOMAS</span>
        <div class="container h-100 p-0">
            <div class="row h-100">




<?php

if (!$actif)
{
    echo 'La commande des pulls est désactivée';
}
elseif (!$datecheck)
{
    echo 'La période de commande des pulls est maintenant dépassée.';
}
elseif ($show == "1classe")
{

?>

              <!-- ETAPE 1 - CLASSE -->
              <div class="col-md-12 my-auto">

                <div class="bg-dark p-4">

                  <h2 class="text-center text-primary">
                    <strong><strong><strong>COMMANDE DES PULLS DE CLASSE</strong> </strong> </strong> <!-- <span class="badge badge-warning">Page en construction</span> -->
                  </h2>

                  <span class="m-1"></span>

                  <p class="text-justify">
                    <strong>Bienvenue sur la page de commande des pulls.</strong><br>
                    <br>
                    Pour commander le pull de votre classe suivez les étapes suivantes, vous en aurez pour <strong>quelques minutes</strong>.<br>
                    <br>
                    À la fin de ce formulaire vous <strong>pourrez vérifier vos informations</strong> avant de les envoyer et vous recevrez 
                    un <em>Numéro de Commande</em> à <strong>conserver</strong> (Vous en recevrez une copie par mail).<br>
                    Si jamais vous n'avez pas reçu ce mail (pensez à vérifier vos spam), retrouvez votre <em>Numéro de Commande</em> <a href="?getc">ici</a> avec votre Nom.<br>
                    <br>
                    Le prix des pulls est different pour chaque classe, cette page vous indiquera un <strong>prix en fonction de votre classe</strong>,
                    si vous pensez qu'il y a une erreur, contactez un membre du bureau.<br>
                    Si vous êtes <strong>Adhérent</strong> vous pourrez bénéficier d'une <strong>réduction de 5€</strong> sur une seule commande.<br>
                    Vous pourrez régler votre pull soit par <strong>chèque</strong> soit par <strong>HelloAsso</strong>, et vous <strong>devrez renseigner votre <em>Numéro de Commande</em></strong>
                    (au dos du chèque ou sur le formulaire HelloAsso).<br>
                    Si vous avez payé en renseignant un mauvais Numéro de Commande, prévenez rapidement un membre du bureau avec si possible
                    votre <em>Numéro de Commande</em> et celui erroné.<br>
                    <br>
                    Vous avez la possibilité de faire plusieures commandes au même nom, mais vous ne pourrez avoir
                    la réduction d'Adhérent que pour une <strong>seule d'entre elles</strong>.<br>
                    <br>
                    Vous pouvez consulter votre(/vos) commande(s) en cliquant <a href="?getc">ici</a>, à partir de votre Nom ou du <em>Numéro de Commande</em>.<br>
                    Si jamais vous ne trouvez plus votre commande, contactez un membre du bureau.<br>
                    <br>
                    Cette page a été faite avec <strong>amour</strong> par des Paul et Thomas en parallèle des cours,
                    malgré le soin que nous y avons porté il se peut que des erreurs et bugs soient passé inaperçu,
                    merci de prevenir un membre du bureau si vous en croisez.
                  </p>

                  <hr>

                  <p class="text-justify">
                    Afin de pouvoir commander votre pull de classe, merci d'indiquer le pull de quelle classe vous souhaitez commander.
                  </p>

                  <hr>
                  
                  <form method="get" id="classe">
                    <label for="Classe">Indiquer la classe</label>
                    <div class="input-group mb-3">
                      <select class="form-control border-success" id="Classe" name="Classe" required>

<?php
foreach (settings::p('pulls_pulls') as $classe => $infos)
{
    if (isset($_SESSION['pulls']['post']['Classe']) && $_SESSION['pulls']['post']['Classe'] == $classe)
    {
        $ckd = ' selected="selected" ';
    }
    else
    {
        $ckd = '';
    }
    echo '<option value="'.$classe.'"'.$ckd.'>'.ucfirst($classe).'</option>';
}
?>

    
                      </select>
                      <div class="input-group-append">
                        <button class="btn btn-success" type="submit" name="next" value="1classe">Suivant</button>
                      </div>
                    </div>

                  </form>

                  <hr>

                  <div class="btn-group" role="group">
                      <a href="?cancel" class="btn btn-outline-primary">
                        Annuler
                      </a>
                      <a href="?getc" class="btn btn-primary">
                        Consulter ma(/mes) commande(s)
                      </a>
                    </div>

                </div>

                <div class="progress">
                  <div class="progress-bar" style="width: 16%">
                  </div>
                </div>

              </div>

<?php

}
elseif($show == "2multi")
{

?>

              <!-- ETAPE 2 - 1 OU PLUSIEURS PULLS -->
              <div class="col-md-12 my-auto">

                  <div class="bg-dark p-4">
  
                    <h2 class="text-center text-primary">
                      <strong>COMMANDE DES PULLS DE CLASSE</strong> 
                    </h2>
  
                    <span class="m-1"></span>
  
                    <p class="text-justify">
                      Si vous pensez que le prix affiché pour votre pull de classe n'est <strong>pas correct</strong>,
                      contactez un membre du bureau.<br>
                      <br>
                      En tant qu'adhérent au BDE vous avez le droit à <strong>5€ de réduction</strong> sur <u>une seule commande</u>.
                    </p>
  
                    <hr>

                    <div class="row justify-content-center">

                      <div class="col-lg-6">
                          <div class="card text-center m-x-3 border-success bg-dark">
                            <div class="card-header"><span class="h1"><?php if (isset($_SESSION['pulls']['post']['Classe'])) { echo $_SESSION['pulls']['post']['Classe']; } ?></span></div>
                            <div class="card-body">
                              <h1 class="display-2 text-success"><strong><?php

if (isset($_SESSION['pulls']['post']['Classe']))
{
    echo getPrixInt($_SESSION['pulls']['post']['Classe'])['nadh'].'€<span class="h1">'.getPrixDecimal($_SESSION['pulls']['post']['Classe'])."</span>";
}
                              ?></strong></h1>
                              <p class="text-muted">5€ de réduction si vous êtes Adhérent au BDE.</p>
                              <a href="?next=2multi" class="btn btn-success">Commander</a>
                            </div>
                          </div>
                      </div>
                      
                      <!-- <div class="col-lg-6">
                          <div class="card text-center m-x-3 border-success bg-dark">
                              <div class="card-header">COMMANDE MULTIPLE</div>
                              <div class="card-body">
                                <h1 class="display-2 text-success"><strong>29€50 <small class="h2">/pull</small></strong></h1>
                                <p class="text-muted">-5€ pour <strong>UN SEUL</strong> pull si vous êtes Adhérent au BDE.</p>
                                <div class="btn-group" role="group">
                                  <button type="button" class="btn btn-outline-success" disabled>Nombre de pulls</button>
                                  <a class="btn btn-success" href="?next=2multi&num=2">2</a>
                                  <a class="btn btn-success" href="?next=2multi&num=3">3</a>
                                  <a class="btn btn-success" href="?next=2multi&num=4">4</a>
                                  <a class="btn btn-success" href="?next=2multi&num=5">5</a>
                                  <a class="btn btn-success" href="?next=2multi&num=6">6</a>
                                  <a class="btn btn-success" href="?next=2multi&num=7">7</a>
                                  <a class="btn btn-success" href="?next=2multi&num=8">8</a>
                                </div>
                              </div>
                            </div>
                      </div> -->

                    </div>

                    <hr>

                    <div class="btn-group" role="group">
                      <a href="?cancel" class="btn btn-outline-primary">
                        Annuler
                      </a>
                      <a href="?prev" class="btn btn-outline-primary">
                        Précédent
                      </a>
                    </div>
  
                  </div>
  
                  <div class="progress">
                    <div class="progress-bar" style="width: 33%">
                    </div>
                  </div>
  
                </div>

                <?php

}
elseif($show == "3adh")
{

?>
                <!-- ETAPE 3 - IDENTIFICATION ADHERENT -->
                <div class="col-md-12 my-auto">

                    <div class="bg-dark p-4">
    
                      <h2 class="text-center text-primary">
                      <strong>COMMANDE DES PULLS DE CLASSE</strong> 
                      </h2>
    
                      <span class="m-1"></span>
    
                      <p class="text-justify">
                       Si vous souhaitez profiter de la <strong>réduction pour les adhérents au BDE (-5€)</strong> vous devez vous identifier.<br>
                       <br>
                       <u>Rappel:</u> Vous ne pourrez vous identifier que sur <strong>une seule commande</strong>, si vous souhaitez en faire plusieures,
                       <strong>NE VOUS IDENTIFIEZ PAS</strong> pour les suivantes.<br>
                       <br>
                       Si vous pensez que quelqu'un a utilisé votre numéro d'Adhérent sans votre permission, contactez un membre du bureau.<br>
                       Si vous usurper l'identifiant de quelqu'un d'autre, votre commande sera annulée.
                      </p>
    
                      <hr>


<?php

if (isset($erreur))
{
?>


    <div class="alert alert-danger" role="alert">
        <?php echo $erreur; ?>
    </div>

<?php
}

?>
  
                      <div class="row">
  
                        <div class="col-md-6">
                          <form method="get">
                            <div class="input-group mb-3">
                              <input type="text" name="IDA" class="form-control border-success" placeholder="Numéro d'adhérent" required value="<?php
                              
if (isset($_SESSION['pulls']['post']['IDA']))
{
  echo $_SESSION['pulls']['post']['IDA'];
}
                              
                              
                              ?>">
                              <div class="input-group-append">
                                <button class="btn btn-success" type="submit" name="next" value="3adh">M'identifier</button>
                              </div>
                            </div>
                          </form>
                        </div>
                        
                        <div class="col-md-6">
                          <a href="?next=3adh" class="btn btn-warning btn-block">Je ne suis pas Adhérent / Je ne m'identifie pas</a>
                        </div>
  
                      </div>

                      <hr class="mt-0">

                      <div class="btn-group" role="group">
                        <a href="?cancel" class="btn btn-outline-primary">
                          Annuler
                        </a>
                        <a href="?prev" class="btn btn-outline-primary">
                          Précédent
                        </a>
                      </div>
    
                    </div>
    
                    <div class="progress">
                      <div class="progress-bar" style="width: 50%">
                      </div>
                    </div>
    
                  </div>

<?php

}
elseif($show == "4form")
{

?>

                <!-- ETAPE 4 - FORMULAIRE -->
                <div class="col-md-12 my-auto">

                    <div class="bg-dark p-4">
    
                      <h2 class="text-center text-primary">
                        <strong>COMMANDE DES PULLS DE CLASSE</strong> 
                      </h2>
    
                      <span class="m-1"></span>
    
                      <p class="text-justify">
                        Merci de compléter le formulaire ci-dessous pour commander votre pull.<br>
                        L'adresse mail ne sera utilisée que pour vous communiquer votre numéro de commande, 
                        elle ne sera pas enregistrée dans notre base de donnée.<br>
                        <br>
                        Vous pourrez <strong>vérifier vos information</strong> à la page suivante avant de les confirmer.
                      </p>
    
                      <hr>
                      <?php

if (isset($_SESSION['pulls']['post']['IDA']) && $_SESSION['pulls']['post']['IDA'] != NULL)
{
?>


    <div class="alert alert-success" role="alert">
        <strong>Vous êtes connecté :</strong> <?php echo $_SESSION['pulls']['post']['IDA'].
                          " (".$gestion_adherents->getAdherent($_SESSION['pulls']['post']['IDA'])->getNom().
                          ", ".$gestion_adherents->getAdherent($_SESSION['pulls']['post']['IDA'])->getPrenom().
                          ")"; ?>
    </div>

<?php
}

?>

<?php

if (isset($erreur))
{
?>


    <div class="alert alert-danger" role="alert">
        <?php echo $erreur; ?>
    </div>

<?php
}

?>

                      <form method="get">
                          <div class="form-row">
                            <div class="form-group col-md-6">
                              <label for="Nom">Nom*</label>
                              <input type="text" class="form-control" id="Nom" name="Nom" placeholder="Nom" required value="<?php
                              
if (isset($_SESSION['pulls']['post']['Infos']['Nom']))
{
  echo $_SESSION['pulls']['post']['Infos']['Nom'];
}
                              
                              
                              ?>">
                            </div>
                            <div class="form-group col-md-6">
                              <label for="Prénom">Prénom*</label>
                              <input type="text" class="form-control" id="Prenom" name="Prenom" placeholder="Prénom" required value="<?php
                              
if (isset($_SESSION['pulls']['post']['Infos']['Prenom']))
{
  echo $_SESSION['pulls']['post']['Infos']['Prenom'];
}
                              
                              
                              ?>">
                            </div>
                          </div>

                          <div class="form-group">
                            <label for="Email">Adresse mail</label>
                            <input type="email" class="form-control" id="Email" name="Email" placeholder="Adresse Mail" required value="<?php
                              
if (isset($_SESSION['pulls']['post']['Infos']['Email']))
{
  echo $_SESSION['pulls']['post']['Infos']['Email'];
}
                              
                              
                              ?>">
                            <small>Votre adresse mail ne sera pas sauvegardée,
                            elle sera utilisée pour vous envoyer votre numéro de commande ainsi que le lien de paiement.</small>
                          </div>
<?php

if (ChampSurnom())
{


?>


                          <div class="form-group">
                            <label for="Surnom">Surnom</label>
                            <input type="text" class="form-control" id="Surnom" name="Surnom" placeholder="Surnom" value="<?php
                              
if (isset($_SESSION['pulls']['post']['Infos']['Surnom']))
{
  echo $_SESSION['pulls']['post']['Infos']['Surnom'];
}
                              
                              
                              ?>">
                            <small>L'option surnom est activée pour votre classe, vous pouvez en renseigner un.</small>
                          </div>

<?php
}
?>


                          <div class="form-group w-25">
                            <label for="Taille">Taille*</label>
                            <select class="form-control" id="Taille" name="Taille" required>


<?php

if (isset($_SESSION['pulls']['post']['Classe']))
{
  foreach (settings::p('pulls_pulls')[$_SESSION['pulls']['post']['Classe']]['Taille'] as $taille)
  {
    if (isset($_SESSION['pulls']['post']['Infos']['Taille']) && $_SESSION['pulls']['post']['Infos']['Taille'] == $taille)
    {
        $ckd = ' selected="selected" ';
    }
    else
    {
        $ckd = '';
    }
    echo '<option value="'.$taille.'"'.$ckd.'>'.ucfirst($taille).'</option>';
  }
}
?>


                            </select>
                          </div>

                          <button type="submit" name="next" value="4form" class="btn btn-success">Suivant</button><br>
                          <small>* Champs requis / Vous pourrez vérifier vos informations.</small>
                        </form>

                      <hr>

                      <div class="btn-group" role="group">
                        <a href="?cancel" class="btn btn-outline-primary">
                          Annuler
                        </a>
                        <a href="?prev" class="btn btn-outline-primary">
                          Précédent
                        </a>
                      </div>
    
                    </div>
    
                    <div class="progress">
                      <div class="progress-bar" style="width: 76%">
                      </div>
                    </div>
    
                  </div>



<?php

}
elseif($show == "5verif")
{

?>

                <!-- ETAPE 5 - VERIFICATION -->
                <div class="col-md-12 my-auto">

                  <div class="bg-dark p-4">
  
                    <h2 class="text-center text-primary">
                      <strong>COMMANDE DES PULLS DE CLASSE</strong> 
                    </h2>
  
                    <span class="m-1"></span>
  
                    <p class="text-justify">
                      Merci de <strong>vérifier les informations</strong> suivantes avant de confirmer,
                      vous ne pourrez <strong>pas revenir en arrière</strong> après cette étape.
                    </p>
  
                    <hr>

                    <?php

if (isset($erreur))
{
?>


    <div class="alert alert-danger" role="alert">
        <?php echo $erreur; ?>
    </div>

<?php
}

?>

                    <table class="table table-hover table-sm">
                      <tbody>
                        <tr>
                          <th scope="row">Nom</th>
                          <td><?php if (isset($_SESSION['pulls']['post']['Infos'])) { echo $_SESSION['pulls']['post']['Infos']['Nom']; } ?></td>
                        </tr>
                        <tr>
                          <th scope="row">Prénom</th>
                          <td><?php if (isset($_SESSION['pulls']['post']['Infos'])) { echo $_SESSION['pulls']['post']['Infos']['Prenom']; } ?></td>
                        </tr>
                        <tr>
                          <th scope="row">Adresse mail</th>
                          <td><?php if (isset($_SESSION['pulls']['post']['Infos'])) { echo $_SESSION['pulls']['post']['Infos']['Email']; } ?></td>
                        </tr>
                        <tr>
                          <th scope="row">Classe</th>
                          <td><?php if (isset($_SESSION['pulls']['post']['Classe'])) { echo $_SESSION['pulls']['post']['Classe']; } ?></td>
                        </tr>
                        

<?php
if (isset($_SESSION['pulls']['post']['IDA']) && $_SESSION['pulls']['post']['IDA'] != NULL)
{
?>

                        <tr>
                          <th scope="row">Prix</th>
                          <td><span class="badge-primary p-1 h5"><strong><?php if (isset($_SESSION['pulls']['post']['Classe'])) { echo getPrixInt($_SESSION['pulls']['post']['Classe'])['adh']."€".getPrixDecimal($_SESSION['pulls']['post']['Classe']); } ?></strong></span></td>
                        </tr>
                        <tr>
                          <th scope="row">Adhérent</th>
                          <td><?php echo $_SESSION['pulls']['post']['IDA'].
                          " (".$gestion_adherents->getAdherent($_SESSION['pulls']['post']['IDA'])->getNom().
                          ", ".$gestion_adherents->getAdherent($_SESSION['pulls']['post']['IDA'])->getPrenom().
                          ")"; ?></td>
                        </tr>

<?php
}
else
{
?>
                        <tr>
                          <th scope="row">Prix</th>
                          <td><span class="badge-primary p-1 h5"><strong><?php if (isset($_SESSION['pulls']['post']['Classe'])) { echo getPrixInt($_SESSION['pulls']['post']['Classe'])['nadh']."€".getPrixDecimal($_SESSION['pulls']['post']['Classe']); } ?></strong></span></td>
                        </tr>
<?php
}
?>


                        <tr>
                          <th scope="row">Taille</th>
                          <td><?php if (isset($_SESSION['pulls']['post']['Infos'])) { echo $_SESSION['pulls']['post']['Infos']['Taille']; } ?></td>
                        </tr>

<?php
if (ChampSurnom())
{
?>

                        <tr>
                          <th scope="row">Surnom</th>
                          <td><?php
                          if (isset($_SESSION['pulls']['post']['Infos']) && $_SESSION['pulls']['post']['Infos']['Surnom'] != NULL)
                          { 
                            echo $_SESSION['pulls']['post']['Infos']['Surnom'];
                          }
                          else
                          {
                            echo '-';
                          }
                          ?></td>
                        </tr>
<?php
}
?>

                      </tbody>
                    </table>

                    <div class="text-center">
                      <a href="?next=5verif" class="btn btn-success">Confirmer ces informations</a>
                    </div>

                    <hr>

                    <div class="btn-group" role="group">
                      <a href="?cancel" class="btn btn-outline-primary">
                        Annuler
                      </a>
                      <a href="?prev" class="btn btn-outline-primary">
                        Précédent
                      </a>
                    </div>
  
                  </div>
  
                  <div class="progress">
                    <div class="progress-bar" style="width: 83%">
                    </div>
                  </div>

<?php

}
elseif($show == "6confirm")
{

?>


                <!-- ETAPE 6 - CONFIRMATION -->
                <div class="col-md-12 my-auto">

                  <div class="bg-dark p-4">
  
                    <h2 class="text-center text-primary">
                      <strong>COMMANDE DES PULLS DE CLASSE</strong> 
                    </h2>
  
                    <span class="m-1"></span>
  
                    <p class="text-justify">
                      Votre commande a bien été enregistrée.<br>
                      <br>
                      Vous disposez de <strong>15 jours pour payer</strong> votre pull, nous vous conseillons de <strong>payer via le formulaire HelloAsso</strong>
                      disponible ci-dessous.<br>
                      Vous <strong>devez renseigner</strong>, soit au dos de votre chèque soit sur le formulaire HelloAsso, votre <em>Numéro de Commande</em>.<br>
                      <u>Rappel:</u> Si vous avez payé en renseignant un mauvais <em>Numéro de Commande</em>, prévenez rapidement un membre du bureau avec si possible
                      votre <em>Numéro de Commande</em> et celui erroné.<br>
                      <br>
                      Le prix à payer (visible dans le tableau ci-dessous) <strong>inclut la réduction</strong> d'adhérent si vous vous êtes identifié.
                      <br>
                      <br>
                      Pas de panique, le bouton HelloAsso peut mettre quelques secondes à apparaître !<br>
                      Si vous quittez cette page, vous pourrez retrouver le lien de paiement dans le mail qui vous a été envoyé et
                      sur la page de récupération de commande (via votre Nom, ou le Numéro de Commande).
                    </p>
  
                    <hr>

    <div class="alert alert-secondary" role="alert">
      <h4 class="alert-heading">Commande effectuée !</h4>
      <p>Voici votre numéro de commande: <strong class="badge-primary p-1 h5">  <?php if (isset($IDC)) { echo $IDC; } ?>  </strong></p>
      <hr>
      <p class="mb-0">Votre numéro de commande doit être indiqué au dos de votre chèque si vous choisissez ce moyen de paiement.</p>
    </div>

                    <table class="table table-hover table-sm">
                      <tbody>
                       <tr>
                          <th scope="row">Numéro de commande</th>
                          <td><?php if (isset($IDC)) { echo $IDC; } ?></td>
                        </tr>
                        <tr>
                          <th scope="row">Nom</th>
                          <td><?php if (isset($inscription_infos)) { echo $inscription_infos['Nom']; } ?></td>
                        </tr>
                        <tr>
                          <th scope="row">Prénom</th>
                          <td><?php if (isset($inscription_infos)) { echo $inscription_infos['Prenom']; } ?></td>
                        </tr>
                        <tr>
                          <th scope="row">Classe</th>
                          <td><?php if (isset($inscription_infos)) { echo $inscription_infos['Classe']; } ?></td>
                        </tr>
                        

<?php
if (isset($inscription_infos) && $inscription_infos['IDA'] != NULL)
{
?>

                        <tr>
                          <th scope="row">Prix</th>
                          <td><span class="badge-primary p-1 h5"><strong><?php if (isset($inscription_infos['Classe'])) { echo getPrixInt($inscription_infos['Classe'])['adh']."€".getPrixDecimal($inscription_infos['Classe']); } ?></strong></span></td>
                        </tr>
                        <tr>
                          <th scope="row">Adhérent</th>
                          <td><?php echo $inscription_infos['IDA'].
                          " (".$gestion_adherents->getAdherent($inscription_infos['IDA'])->getNom().
                          ", ".$gestion_adherents->getAdherent($inscription_infos['IDA'])->getPrenom().
                          ")"; ?></td>
                        </tr>

<?php
}
else
{
?>
                        <tr>
                          <th scope="row">Prix</th>
                          <td><span class="badge-primary p-1 h5"><strong> <?php if (isset($inscription_infos['Classe'])) { echo getPrixInt($inscription_infos['Classe'])['nadh']."€".getPrixDecimal($inscription_infos['Classe']); } ?></strong></span></td>
                        </tr>
<?php
}
?>


                        <tr>
                          <th scope="row">Taille</th>
                          <td><?php if (isset($inscription_infos)) { echo $inscription_infos['Taille']; } ?></td>
                        </tr>

<?php
if (ChampSurnom())
{
?>

                        <tr>
                          <th scope="row">Surnom</th>
                          <td><?php
                          if (isset($inscription_infos) && $inscription_infos['Surnom'] != NULL)
                          { 
                            echo $inscription_infos['Surnom'];
                          }
                          else
                          {
                            echo '-';
                          }
                          ?></td>
                        </tr>
<?php
}
?>

                      </tbody>
                    </table>

                    <hr>

                    <div style="text-align: center;">
                      <iframe id="haWidgetButton" src="<?php
                      if (isset($inscription_infos['Classe']))
                      { 
                        if ($inscription_infos['IDA'] != NULL)
                        {
                          echo settings::p('pulls_pulls')[$Classe]['Lien_adh'];
                        }
                        else
                        {
                          echo settings::p('pulls_pulls')[$Classe]['Lien'];
                        }
                      }
                      ?>" style="border: none;"></iframe>
                    </div>
  
                  </div>
  
                  <div class="progress">
                    <div class="progress-bar" style="width: 100%">
                    </div>
                  </div>
  

<?php
}
elseif ($show == 'getc')
{
?>
                <!-- RECUPERER SES COMMANDES -->
                <div class="col-md-12 my-auto">

                  <div class="bg-dark p-4">

                    <h2 class="text-center text-primary">
                      <strong>COMMANDE DES PULLS DE CLASSE</strong> 
                    </h2>

                    <span class="m-1"></span>

                    <p class="text-justify">
                      Vous pouvez récuperer les informations de commande à l'aide de ce formulaire via votre Nom
                      ou votre Numéro de Commande (que vous avez normalement reçu par mail).<br>
                      <br>
                      Toutes les demande de récupération sont enregistrées et tout abus sera sanctionné (ban ip pour cette fonctionnalité).   
                    </p>

                    <hr>


<?php

if (isset($erreur))
{
?>


  <div class="alert alert-danger" role="alert">
    <?php echo $erreur; ?>
  </div>

<?php
}

?>
                  <div class="row">
                    <div class="col-md-6">
                      <form method="get">
                        <div class="input-group mb-3">
                          <input type="text" class="form-control border-success" id="Nom" name="Nom" placeholder="Nom" required>
                          <div class="input-group-append">
                            <button class="btn btn-success" type="submit" name="getc" value="nom">Cherche par Nom</button>
                          </div>
                        </div>
                      </form>
                    </div>
                    <div class="col-md-6">
                      <form method="get">
                        <div class="input-group mb-3">
                          <input type="text" class="form-control border-success" id="IDC" name="IDC" placeholder="Numéro de Commande" required>
                          <div class="input-group-append">
                            <button class="btn btn-success" type="submit" name="getc" value="idc">Chercher par Numéro de Commande</button>
                          </div>
                        </div>

                      </form>
                    </div>
                  </div>

                      <small>* Champs requis / Toutes les demandes sont enregistrées pour eviter les abus.</small>

                    <hr>

                    <div class="btn-group" role="group">
                      <a href="?back" class="btn btn-outline-primary">
                        Retour à la page de commande
                      </a>
                    </div>

                  </div>

                </div>
<?php
}
elseif ($show == 'showc')
{
?>

                <!-- AFFICHAGE DES COMMANDES -->
                <div class="col-md-12 my-auto">

                  <div class="bg-dark p-4">
  
                    <h2 class="text-center text-primary">
                      <strong>COMMANDE DES PULLS DE CLASSE</strong> 
                    </h2>
  
                    <span class="m-1"></span>
  
                    <p class="text-justify">
                      Si des informations ne sont pas correctes, contactez un membre du bureau pour les rectifier.<br>
                      <br>
                      Rappel: Si vous avez payé en renseignant un mauvais Numéro de Commande, prévenez rapidement un membre du bureau avec si possible
                      votre Numéro de Commande et celui erroné.<br>
                    </p>
  
                    <hr>
                    

                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th scope="col">Commande</th>
                          <th scope="col">Nom</th>
                          <th scope="col">Prénom</th>
                          <th scope="col">Adhérent</th>
                          <th scope="col">Classe</th>
                          <th scope="col">Prix</th>
                          <th scope="col">Taille</th>
                          <th scope="col">Surnom</th>
                          <th scope="col">Lien de paiement</th>
                        </tr>
                      </thead>
                      <tbody>

<?php

if (isset($commandes))
{
  foreach($commandes as $cmd)
  {
    if ($cmd['Surnom'] == NULL)
    {
      $surnom = '-';
    }
    else
    {
      $surnom = $cmd['Surnom'];
    }

    if ($cmd['IDA'] == NULL)
    {
      $adh = '-';
      $prix = getPrixInt($cmd['Classe'])['nadh']."€".getPrixDecimal($cmd['Classe']);
      $lien = settings::p('pulls_pulls')[$cmd['Classe']]['Lien'];
    }
    else
    {
      $adh = " (".$gestion_adherents->getAdherent($cmd['IDA'])->getNom().
      ", ".$gestion_adherents->getAdherent($cmd['IDA'])->getPrenom().
      ")";
      $prix = getPrixInt($cmd['Classe'])['adh']."€".getPrixDecimal($cmd['Classe']);
      $lien = settings::p('pulls_pulls')[$cmd['Classe']]['Lien_adh'];
    }

    ?>

                        <tr>
                          <th scope="row"><?php echo $cmd['IDC'];?></th>
                          <td><?php echo $cmd['Nom']; ?></td>
                          <td><?php echo $cmd['Prenom']; ?></td>
                          <td><?php echo $adh; ?></td>
                          <td><?php echo $cmd['Classe']; ?></td>
                          <td><?php echo $prix; ?></td>
                          <td><?php echo $cmd['Taille']; ?></td>
                          <td><?php echo $surnom; ?></td>
                          <td><iframe id="haWidgetButton" src="<?php echo $lien; ?>" style="border: none;"></iframe></td>
                        </tr>

    <?php


  }


}
 
?>


                      </tbody>
                    </table>

                    <hr>

                    <div class="btn-group" role="group">
                      <a href="?back" class="btn btn-outline-primary">
                        Retour à la page de commande
                      </a>
                      <a href="?prev" class="btn btn-outline-primary">
                        Précédent
                      </a>
                    </div>
  
                  </div>

<?php
}
else
{
    echo "Une erreur est survenue, aucun contenu à afficher, si l'erreur perciste, merci de contacter un admin.";
}
?>
                </div>

            </div>
          </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>