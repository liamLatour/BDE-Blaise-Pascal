<?php

class image
{
    private $_data,
            $_name,
            $_type;

    public function __construct($data, $name, $type)  
    {
        $this->_data = $data;
        $this->_name = $name;
        $this->_type = $type;
    }      

    public function getData()
    {
        return $this->_data;
    }

    public function getName()
    {
        return $this->_name;
    }
    
    public function getType()
    {
        return $this->_type;
    }
}


?>