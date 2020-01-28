<?php

/**
 * Class: GESTION_MAILS
 * 
 * elle doit gerer l'edition et l'envoi de mail
 * ainsi que la gestion de code de verification par mail
 */


class gestion_mails
{

    private $_apiInstance,
            $_apiInstance_Account,
            $_IP;

    public function __construct($IP)
    {
        $this->_IP = $IP;
        
        $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', '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');
        $this->_apiInstance = new SendinBlue\Client\Api\SMTPApi(
            new GuzzleHttp\Client(),
            $config
        );
        $this->_apiInstance_Account = new SendinBlue\Client\Api\AccountApi(
            new GuzzleHttp\Client(),
            $config
        );
    }

    public function getCredits()
    {
        return $this->_apiInstance_Account->getAccount()->getPlan()[0]->getCredits();
    }


    private function sendMail(string $email, string $name, int $templateId, array $params)
    {
        $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail();
        $sendSmtpEmail['to'] = array(array('email'=> $email, 'name'=> $name));
        $sendSmtpEmail['templateId'] = $templateId;
        $sendSmtpEmail['params'] = $params;

        try
        {
            $result = $this->_apiInstance->sendTransacEmail($sendSmtpEmail);
            return true;
        }
        catch (Exception $e)
        {
            return $e;
        }
    }

    public function mail_Adhesion($lienhelloasso, $adherent) // 9
    {
        $email = $adherent->getEmail();
        $name = $adherent->getPNom();

        $IDA = $adherent->getIDA();
        $Nom = $adherent->getNom();
        $Prenom = $adherent->getPrenom();
        $Classe = $adherent->getClasse();
        $Status = $adherent->getStatusString();
        $Role = $adherent->getRoleString();

        $params = [
            'IDA' => $IDA,
            'NOM' => $Nom,
            'PRENOM' => $Prenom,
            'CLASSE' => $Classe,
            'EMAIL' => $email,
            'STATUS' => $Status,
            'ROLE' => $Role,

            'LINK' => $lienhelloasso
        ];

        $this->sendMail($email, $name, 9, $params);
    }

    public function mail_ConfirmationPaiementAdhesion($adherent) // 11
    {
        $email = $adherent->getEmail();
        $name = $adherent->getPNom();

        $IDA = $adherent->getIDA();
        $Nom = $adherent->getNom();
        $Prenom = $adherent->getPrenom();
        $Classe = $adherent->getClasse();
        $Status = $adherent->getStatusString();
        $Role = $adherent->getRoleString();

        $params = [
            'IDA' => $IDA,
            'NOM' => $Nom,
            'PRENOM' => $Prenom,
            'CLASSE' => $Classe,
            'EMAIL' => $email,
            'STATUS' => $Status,
            'ROLE' => $Role
        ];

        $this->sendMail($email, $name, 11, $params);
    }

    public function mail_Annulation($adherent) // 10
    {
        $email = $adherent->getEmail();
        $name = $adherent->getPNom();

        $IDA = $adherent->getIDA();

        $params = [
            'IDA' => $IDA
        ];

        $this->sendMail($email, $name, 10, $params);
    }

    public function mail_AuthKey($link, $code, $adherent) // 8
    {
        $resetpass = "https://auth.bde-bp.fr?resetpass";
        
        $email = $adherent->getEmail();
        $name = $adherent->getPNom();

        $params = [
            'IP' => $this->_IP,
            'DATETIME' => date('d-m-Y H:i:s'),

            'RESETPASS' => $resetpass,

            'LINK' => $link,
            'CODE' => $code
        ];

        $this->sendMail($email, $name, 8, $params);
    }

    public function mail_ConfirmPass($link, $adherent) // 7
    {
        $email = $adherent->getEmail();
        $name = $adherent->getPNom();

        $params = [
            'LINK' => $link,

            'IP' => $this->_IP,
            'DATETIME' => date('d-m-Y H:i:s')
        ];

        $this->sendMail($email, $name, 7, $params);
    }

    public function mail_ResetPass($link, $adherent) // 6
    {
        $email = $adherent->getEmail();
        $name = $adherent->getPNom();

        $params = [
            'LINK' => $link,

            'IP' => $this->_IP,
            'DATETIME' => date('d-m-Y H:i:s')
        ];

        $this->sendMail($email, $name, 6, $params);
    }

    public function mail_GetIDA($adherent) // 5
    {
        $email = $adherent->getEmail();
        $name = $adherent->getPNom();

        $IDA = $adherent->getIDA();
        $Nom = $adherent->getNom();
        $Prenom = $adherent->getPrenom();
        $Classe = $adherent->getClasse();
        $Status = $adherent->getStatusString();
        $Role = $adherent->getRoleString();

        $params = [
            'IP' => $this->_IP,
            'DATETIME' => date('d-m-Y H:i:s'),

            'IDA' => $IDA,
            'NOM' => $Nom,
            'PRENOM' => $Prenom,
            'CLASSE' => $Classe,
            'EMAIL' => $email,
            'STATUS' => $Status,
            'ROLE' => $Role
        ];

        $this->sendMail($email, $name, 5, $params);
    }

}





?>