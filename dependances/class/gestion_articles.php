<?php

/**
 * 
 * 
 * INUTILISE
 * 
 * 
 * 
 */

class gestion_articles
{

    private $_bdd,
            $_settings;



    /**
    * CONSTRUCTION
    */
    public function __construct(bdd $bdd)
    {
    $this->_settings = settings::p('gestion_articles');
    $this->_bdd = $bdd;
    }

    private function createArticle(string $titre, string $soustitre, string $description, int $miniatureID, string $datedebut, date $datefin, date $contenu, string $forminputs, string $customdata)
    {

        if (strlen($soustitre) == 0)
        {
            $soustitre = NULL;
        }
        if (strlen($description) == 0)
        {
            $description = NULL;
        }
        if ($miniatureID == 0)
        {
            $miniatureID = NULL;
        }
        if (strlen($forminputs) == 0)
        {
            $forminputs = NULL;
        }
        if (strlen($customdata) == 0)
        {
            $customdata = NULL;
        }
        
        // Enregistre dans la bdd le nouvel article
        // verifie si il a bien été créé
        $req = $this->_bdd->registre()->prepare('INSERT INTO articles
        (Status, Titre, Soustitre, Description, MiniatureID, DateDebut, DateFin, Contenu, FormInputs, CustomData) 
        VALUES(:Status, :Titre, :Soustitre, :Description, :MiniatureID, :DateDebut, :DateFin, :Contenu, :FormInputs, :CustomData)');
        $req->execute([
            'Status' => article::STATUS_ACTIF,
            'Titre' => $titre,
            'Soustitre' => $soustitre,
            'Description' => $description,
            'MiniatureID' => $miniatureID,
            'DateDebut' => $datedebut,
            'DateFin' => $datefin,
            'Contenu' => $contenu,
            'FormInputs' => $forminputs,
            'CustomData' => $customdata
        ]);

        $article = $this->getArticle($article);
        if ($article)
        {
            return true;
        }
        else
        {
            return err::e(err::BDD_ARTICLE);
        }
    }

    public function createArticleHandler(array $post)
    {
        if (isset($post['titre']) && isset($post['datedebut']) && isset($post['datefin']) && isset($post['contenu']))
        {
            $titre = $post['titre'];
            $datedebut = new Date($post['datedebut']);
            $datefin = new Date($post['datefin']);
            $contenu = $post['contenu'];

            if (!isset($post['soustitre']))
            {
                $soustitre = '';
            }
            else
            {
                $soustitre = $post['soustitre'];
            }

            if (!isset($post['description']))
            {
                $description = '';
            }
            else
            {
                $description = $post['description'];
            }

            if (!isset($post['miniatureID']))
            {
                $miniatureID = 0;
            }
            else
            {
                $miniatureID = $post['miniatureID'];
            }

            if (!isset($post['forminputs']))
            {
                $forminputs = '';
            }
            else
            {
                $forminputs = article::FormInputsFormattage($post['forminputs']);
            }

            if (!isset($post['customdata']))
            {
                $customdata = '';
            }
            else
            {
                $customdata = $post['customdata'];
            }

            if (err::c($forminputs))
            {
                return $this->createArticle(
                    $titre,
                    $soustitre,
                    $description,
                    $miniatureID,
                    $datedebut,
                    $datefin,
                    $contenu,
                    $forminputs,
                    $customdata);
            }
            else
            {
                return $forminputs;
            }
        }
        else
        {
            return err::e(err::ALLINFOSNEEDD);
        }
    }

    private function editArticle(article $article)
    {
        if ($this->getArticle($article->getID()))
        {
            $req = $this->_bdd->registre()->prepare('UPDATE articles
            SET Status = :Status, Titre = :Titre, Soustitre = :Soustitre, 
            Description = :Description, MiniatureID = :MiniatureID, 
            DateDebut = :DateDebut, DateFin = :DateFin, Contenu = :Contenu, 
            FormInputs = :FormInputs, CustomData = :CustomData 
            WHERE ID = :ID');
            $res = $req->execute([
                'ID' => $article->getID(),
                'Status' => $article->getStatus(),
                'Titre' => $article->getTitre(),
                'Soustitre' => $article->getSoustitre(),
                'Description' => $article->getDescription(),
                'MiniatureID' => $article->getMiniatureID(),
                'DateDebut' => $article->getDateDebut(),
                'DateFin' => $article->getDateFIn(),
                'Contenu' => $article->getContenu(),
                'FormInputs' => $article->getFormInputs(),
                'CustomData' => $article->getCustomData()
            ]);

            return $res;
        }
        else
        {
            return err::e(err::NEXISTE_PAS);
        }
    }

    public function delArticle(int $ID)
    {       
        $req = $this->_bdd->registre()->prepare('DELETE FROM articles WHERE ID = :ID');
        $res = $req->execute([
            'ID' => $ID
        ]);

        return $res;
    }

    /** =======================================================================================
     * RECUPERER ET CREER UN OBJET ARTICLE
     * Renvoi un adherent ou false
     */

    // créer une class:article depuis un resultat mysql
    // renvoi un article ou false
    private function createArticleFromReq($req)
    {
        if (isset($req['ID'])) // verifier qu'on a bien un retour
        {
            $donnes_article = [
                'ID' => $req['ID'],
                'Status' => $req['Status'],
                'Titre' => $req['Titre'],
                'Soustitre' => $req['Soustitre'],
                'Description' => $req['Description'],
                'MiniatureID' => $req['MiniatureID'],
                'DateDebut' => $req['DateDebut'],
                'DateFin' => $req['DateFin'],
                'Contenu' => $req['Contenu'],
                'FormInputs' => $req['FormInputs'],
                'CustomData' => $req['CustomData']
            ];
            $article = new article($donnes_article);
            return $article;
        }
        else
        {
            return false;
        }
    }

    // recuperer un article depuis son ID
    public function getArticle(int $ID)
    {
        $req = $this->_bdd->web()->prepare('SELECT * FROM articles WHERE ID = :ID');
        $req->execute([
            'ID' => $ID
        ]);
        $req = $req->fetch(PDO::FETCH_ASSOC);

        return $this->createArticleFromReq($req);
    }

    // recuperer un article depuis son titre
    public function getArticleFromTitre(string $titre)
    {
        $req = $this->_bdd->web()->prepare('SELECT * FROM articles WHERE Titre = :Titre');
        $req->execute([
            'Titre' => $titre
        ]);
        $req = $req->fetch(PDO::FETCH_ASSOC);

        return $this->createArticleFromReq($req);
    }




    /**
     * FORMULAIRE DES ARTICLES
    */

    public function PostForm($post)
    {
        
    }


    /**
     * VERIFICATIONS
     */

    // Verifier titre, soustitre, description
    public function verifyT_ST_DSC(string $string)
    {
        return strlen($string) >= 3 && strlen($string) <= 250;
    }

    public function verifyStatus(int $status)
    {
        return $status == article::STATUS_ACTIF || $status == article::STATUS_INACTIF;
    }


    /** =======================================================================================
     * AUTRES
     */
    public function CountArticlesVisibles()
    {
        $req = $this->_bdd->web()->query('SELECT * FROM articles WHERE Status = '.article::STATUS_ACTIF);
        $req = $req->fetchAll(PDO::FETCH_ASSOC);

        $i = 0;

        foreach($req as $areq)
        {
            $article = $this->createArticleFromReq($areq);
            if ($article && $article->isActif())
            {
                $i++;
            }
        }
        return $i;
    }






}



?>