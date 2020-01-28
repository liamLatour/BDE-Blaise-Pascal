<?php
/**
 * CLASS GESTION_LOGS
 * 
 * Permet la gestion de class:log
 * son objectif est la génération, le stockage, la recuperation, la régéneration, le formattage
 * de logs (=historique des actions en fonction d'une ip)
 */


class gestion_logs
{

    /**
     * Génération et création des logs
     */
    public static function Log(string $ip, int $type, string $titre, $description) // "Logger" une nouvelle action (l'enregistre aussi)
    {
        if (isset($_SESSION['Adherent']))
        {
            $IDA = $_SESSION['Adherent']->getIDA();
        }
        else
        {
            $IDA = 0;
        }
        $log = new log($ip, $type, $titre, $description, $IDA);

        $date = $log->getDate(); // Recuperer objet date

        self::updateFile($log, $date);

        // $_SESSION['Logs']['IDA'] = $IDA;
        // $_SESSION['Logs']['Logs'][] = $log->getLine();
        
        // if (!isset($_SESSION['Logs']['Start']))
        // {
        //     $_SESSION['Logs']['Start'] = date_format($datetime, 'Y-m-d H:i:s'); // reconvertir objet date en string
        // }
        // $_SESSION['Logs']['Last'] = date_format($datetime, 'Y-m-d H:i:s');

        // $this->updateBdd();

        // return $log;
    }

    private static function updateFile(log $log, string $date)
    {
        if (file_exists(__DIR__.'/../../domains/docs/logs/'.$date))
        {
            $logs = file_get_contents(__DIR__.'/../../domains/docs/logs/'.$date);
            $logs = gzuncompress($logs);
            $logs = json_decode($logs, true);
        }
        else
        {
            $logs = [];
        }

        $logs[] = $log->getArray();
        $logs = json_encode($logs);
        $logs = gzcompress($logs);

        file_put_contents(__DIR__.'/../../domains/docs/logs/'.$date, $logs);
    }

    private static function getLogsArray(string $date)
    {
        if (file_exists(__DIR__.'/../../domains/docs/logs/'.$date))
        {
            $logsArray = file_get_contents(__DIR__.'/../../domains/docs/logs/'.$date);
            $logsArray = gzuncompress($logsArray);
            $logsArray = json_decode($logsArray, true);
            return $logsArray;
        }
        else
        {
            return false;
        }
    }


    public static function getLogs(string $date)
    {
        $logsArray = self::getLogsArray($date);
        if ($logsArray === false)
        {
            return false;
        }
        else
        {
            $filepath = __DIR__.'/../../domains/docs/logs/csv_'.$date.'.csv';
            $csv = fopen($filepath, 'w+');
            //fputs($csv, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
            // fputcsv($csv, [
            //     'ADRESSE IP',
            //     'HORODATAGE',
            //     'IDA',
            //     'TYPE',
            //     'TITRE',
            //     'DESCRIPTION'
            // ], ';', '"');
            fputcsv($csv, [
                'ADRESSE IP',
                'HORODATAGE',
                'IDA',
                'TYPE',
                'TITRE',
                'DESCRIPTION'
            ]);
            foreach ($logsArray as $log)
            {
                $ip = $log[log::ARRAY_IP];
                $time = $log[log::ARRAY_TIME];
                $ida = $log[log::ARRAY_IDA];
                $type = $log[log::ARRAY_TYPE];

                switch ($type)
                {
                    case $type == log::TYPE_VIEW:
                        $type = 'VIEW';
                        break;
                    case $type == log::TYPE_COMPTE:
                        $type = 'COMPTE';
                        break;
                    case $type == log::TYPE_ADMIN:
                        $type = 'ADMIN';
                        break;
                    case $type == log::TYPE_ERROR:
                        $type = 'ERREUR';
                        break;
                    case $type == log::TYPE_EVENT:
                        $type = 'EVENEMENT';
                        break;
                    default:
                        $type = '-';
                        break;
                }

                $titre = $log[log::ARRAY_TITRE];
                $description = json_encode(unserialize(base64_decode($log[log::ARRAY_DESCRIPTION])), JSON_PRETTY_PRINT);
                
                
                // fputcsv($csv, [
                //     $ip, $time, $ida, $type, $titre, $description
                // ], ';', '"');

                fputcsv($csv, [
                    $ip, $time, $ida, $type, $titre, $description
                ]);
            }
            fclose($csv);
            $logs = file_get_contents($filepath);
            unlink($filepath);
            return $logs;
        }
    }

    public static function sendLogs(string $date)
    {
        $logs = self::getLogs($date);
        if ($logs)
        {
            header("Content-type: application/octet-stream");
            header("Content-disposition: attachment;filename=".$date.".csv");
            echo $logs;
        }
        else
        {
            return false;
        }
    }

    public static function getAllFiles()
    {
        return scandir(__DIR__.'/../../domains/docs/logs');
    }

    public static function countLines(string $date)
    {
        return sizeof(self::getLogsArray($date));
    }

    public static function getSize(string $logname)
    {
        $size = filesize(__DIR__.'/../../domains/docs/logs/'.$logname);
        return self::human_filesize($size);
    }

    private static function human_filesize($bytes, $decimals = 2)
    {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor))." ".@$sz[$factor];
    }



}



?>