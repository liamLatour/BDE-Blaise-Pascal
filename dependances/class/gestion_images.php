<?php

class gestion_images
{

    private $_bdd;


    /**
     * CONSTRUCTION
     */
    public function __construct(bdd $bdd)
    {
        $this->_bdd = $bdd;
    }

    public function getImage(string $name)
    {
        $req = $this->_bdd->web()->prepare('SELECT picture FROM pictures WHERE name = :name');
        $req->execute([':name' => $name]);
        $req->bindColumn(1, $data, PDO::PARAM_LOB);
        $req->fetch(PDO::FETCH_BOUND);

        $req = $this->_bdd->web()->prepare('SELECT name, type FROM pictures WHERE name = :name');
        $req->execute([':name' => $name]);
        $infos = $req->fetch(PDO::FETCH_ASSOC);

        return new image($data, $infos['name'], $infos['type']);
    }

    public function createTempImage(string $name)
    {
        $req = $this->_bdd->web()->prepare('SELECT picture FROM pictures WHERE name = :name');
        $req->execute([':name' => $name]);
        $req->bindColumn(1, $data, PDO::PARAM_LOB);
        $req->fetch(PDO::FETCH_BOUND);

        $req = $this->_bdd->web()->prepare('SELECT name, file, extension FROM pictures WHERE name = :name');
        $req->execute([':name' => $name]);
        $infos = $req->fetch(PDO::FETCH_ASSOC);

        $file_name = $name.'.'.$infos['extension'];
        $handle = fopen('../../domains/docs/images/temp/'.$file_name, 'w');
        fwrite($handle, $data);
        fclose($handle);
        return $file_name;
    }

    public function delTempImage(string $file_name)
    {
        $file_path = '../../domains/docs/images/temp/'.$file_name;
        unlink($file_path);
    }

    public function getImageAlt(string $name)
    {
        $req = $this->_bdd->web()->prepare('SELECT replace_string FROM pictures WHERE name = :name');
        $req->execute([':name' => $name]);
        return $req->fetch(PDO::FETCH_ASSOC)['replace_string'];
    }

    public function verifImageName(string $name)
    {
        return preg_match("/^([a-z0-9-]{4,64})$/", $name) && $this->uniImageName($name);
    }

    public function uniImageName(string $name)
    {
        $req = $this->_bdd->web()->prepare('SELECT name FROM pictures WHERE name = :name');
        $req->execute([':name' => $name]);
        $infos = $req->fetch(PDO::FETCH_ASSOC);

        return !(bool) $infos;
    }

    public function verifImageType(string $file_extension)
    {
        return in_array($file_extension, ['png', 'jpeg', 'jpg', 'gif']);
    }

    public function uploadImage($name, $file, $alt = NULL)
    {
        if ($this->verifImageName($name))
        {
            $file_name = $file["name"];
            $file_type = $file["type"];
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            if ($this->verifImageType($file_extension))
            {
                $req = $this->_bdd->web()->prepare('INSERT INTO pictures (file, name, extension, type, picture, replace_string) VALUES (?, ?, ?, ?, ?, ?)');   
                $file_open = fopen($file['tmp_name'], 'rb');

                $req->bindParam(1, $file_name);
                $req->bindParam(2, $name);
                $req->bindParam(3, $file_extension);
                $req->bindParam(4, $file_type);
                $req->bindParam(5, $file_open, PDO::PARAM_LOB);
                $req->bindParam(6, $alt);

                $this->_bdd->web()->beginTransaction();
                $result = $req->execute();
                $this->_bdd->web()->commit();

                return $result;
            }
            else
            {
                return err::e(err::IMG_TYPE);
            }
        }
        else
        {
            return err::e(err::IMG_NAME);
        }
    }



}


?>