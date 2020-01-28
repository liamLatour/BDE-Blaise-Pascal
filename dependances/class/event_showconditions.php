<?php

class event_showconditions
{

    private $_date_start,
            $_date_stop,
            $_show;

    public function __construct($date_start, $date_stop, $show)
    {
        $this->_date_start = $date_start;
        $this->_date_stop = $date_stop;
        $this->_show = $show;
    }

    public function show()
    {
        $today = new DateTime();
        $date_start = new DateTime($this->_date_start);
        $date_stop  = new DateTime($this->_date_stop);

        if ($today->getTimestamp() >= $date_start->getTimestamp() && $today->getTimestamp() <= $date_stop->getTimestamp())
        {
            $datecheck = true;
        }
        else
        {
            $datecheck = false;
        }


        return $this->_show && $datecheck;
    }

    public function getVariables()
    {
        return ['date_start' => $this->_date_start, 'date_stop' => $this->_date_stop, 'show' => $this->_show];
    }

    public function exportToArray()
    {
        return $this->getVariables();
    }

    public static function importFromArray($array)
    {
        if (isset($array['date_start']) && isset($array['date_stop']) && isset($array['show']))
        {
            return new self($array['date_start'], $array['date_stop'], $array['show']);
        }
        else
        {
            return NULL;
        }
    }


}

?>