<?php


class event_custominput
{
    const   TYPE_STRING = 101,
            TYPE_TEXT   = 102,
            TYPE_EMAIL  = 103,
            TYPE_SELECT = 104,
            TYPE_NUM    = 105,
            TYPE_TEL    = 106,
            TYPE_DATE   = 107,
            TYPE_TIME   = 108,
            TYPE_DT     = 109,
            TYPE_RADIO  = 110,
            TYPE_CHECKBOXES  = 111;  


    public  $Name,
            $Label,
            $Placeholder,

            $Type,
            $RegEx,
            $ErrorMsg,
            $Obligatoire,

            $Locked,

            $Options,

            $Value;

    public function __construct($Name, $Label, $Placeholder, $Type, $Obligatoire = false, $RegEx = '', $ErrorMsg = '', $Options = [], $Value = NULL)
    {
        $this->Name = $Name;
        $this->Label = $Label;
        $this->Placeholder = $Placeholder;
        $this->Type = $Type;
        $this->RegEx = $RegEx;
        $this->ErrorMsg = $ErrorMsg;
        $this->Obligatoire = $Obligatoire;
        $this->Options = $Options;
        $this->Locked = false;
        if ($this->checkValue($Value))
        {
            $this->Value = $Value;
        }
        else
        {
            $this->Value = NULL; 
        }
    }

    public function addPrefix($prefix)
    {
        $this->Name = slugify($prefix)."_".$this->Name;
    }

    public function setValue($Value)
    {
        if ($this->checkValue($Value))
        {
            $this->Value = $Value;
        }
        else
        {
            $this->Value = NULL; 
        }
    }

    public function checkValue($Value)
    {
        if ($this->Type == self::TYPE_SELECT)
        {
            $search = false;
            $Value = slugify($Value);
            foreach($this->Options as $opt)
            {
                if ($opt['Value'] == $Value)
                {
                    $search = true;
                    break;
                }
            }
            return $search;
        }
        else
        {
            if ($this->RegEx != '' && $this->RegEx != NULL)
            {
                return preg_match($this->RegEx, $Value);
            }
            else
            {
                return true;
            }
        }
    }

    public function lock()
    {
        $this->Locked = true;
    }

    private function getTypeString()
    {
        switch($this->Type)
        {
            case self::TYPE_STRING:
                return "text";
                break;
            case self::TYPE_EMAIL:
                return "email";
                break;
            case self::TYPE_NUM:
                return "number";
                break;
            case self::TYPE_TEL:
                return "tel";
                break;
            case self::TYPE_DATE:
                return "date";
                break;
            case self::TYPE_TIME:
                return "time";
                break;
            case self::TYPE_DT:
                return "datetime";
                break;
            default:
                return false;
                break;
        }
    }

    public function isListType()
    {
        return $this->Type != self::TYPE_TEXT && $this->getTypeString() === false;
    }


    /**
     * 
     * GET INPUT TO PRINT
     * 
     */

    public function getInput($prefix = "")
    {
        if ($prefix != "")
        {
            $prefix = $prefix."_-_";
        }
        if ($this->Type == self::TYPE_SELECT)
        {
            return $this->getSelect($prefix);
        }
        else if ($this->Type == self::TYPE_TEXT)
        {
            return $this->getText($prefix);
        }
        else if ($this->Type == self::TYPE_RADIO)
        {
            return $this->getRadio($prefix);
        }
        else if ($this->Type == self::TYPE_CHECKBOXES)
        {
            return $this->getCheckboxes($prefix);
        }
        else
        {
            return $this->getBasicInput($prefix);
        }
    }

    private function getSelect($prefix)
    {

        if ($this->Locked)
        {
            $disabled.=' disabled="disabled" ';
        }
        else
        {
            $disabled = "";
        }

        $select = '<select '.$this->getBasicProperties($prefix).$disabled.' >'."\n";

        // Placeholder 
        if ($this->Value == NULL)
        {
            $selected = ' selected="selected" default ';
        }
        else
        {
            $selected = '';
        }
        $select .= '<option value="" disabled="disabled" '.$selected.' >'.$this->Placeholder.'</option>'."\n";

        // Options
        foreach ($this->Options as $opt)
        {
            if ($opt['Value'] == $this->Value)
            {
                $selected = ' selected="selected" default ';
            }
            else
            {
                $selected = '';
            }
            
            $select .= '<option value="'.$opt['Value'].'" '.$selected.' >'.$opt['Name'].'</option>'."\n";
        }
        $select .= '</select>'."\n";

        return $this->getLabel($prefix).$select.'</br>';
    }

    private function getBasicInput($prefix)
    {
        return $this->getLabel($prefix).'<input '.$this->getBasicProperties($prefix).' type="'.$this->getTypeString().'" value="'.$this->Value.'"></br>'."\n";
    }

    private function getText($prefix)
    {
        return $this->getLabel($prefix).'<textarea'.$this->getBasicProperties($prefix).'>'.$this->Value.'</textarea></br>'."\n";
    }

    private function getRadio($prefix)
    {
        $radios = '';
        foreach ($this->Options as $opt)
        {
            if ($opt['Value'] == $this->Value)
            {
                $checked = ' checked="checked" ';
            }
            else
            {
                $checked = '';
            }

            if ($this->Obligatoire)
            {
                $required = ' required="required" ';
            }
            else
            {
                $required = '';
            }

            $radios .= '<input name="'.$prefix.$this->Name.'" type="radio" '.$checked.' id="'.$prefix.$this->Name.'-'.$opt['Value'].'" value="'.$opt['Value'].'" '.$required.' >';
            $radios .= '<label for="'.$prefix.$this->Name.'-'.$opt['Value'].'" >'.$opt['Name'].'</label>'."\n";
        }
        return $this->getLabel($prefix).$radios.'</br>';
    }

    private function getCheckboxes($prefix)
    {
        $cb = '';
        foreach ($this->Options as $opt)
        {
            if ($opt['Value'] == $this->Value)
            {
                $checked = ' checked="checked" ';
            }
            else
            {
                $checked = '';
            }

            $cb .= '<input name="'.$prefix.$this->Name.'[]" type="checkbox" '.$checked.' id="'.$prefix.$this->Name.'-'.$opt['Value'].'" value="'.$opt['Value'].'" >';
            $cb .= '<label for="'.$prefix.$this->Name.'-'.$opt['Value'].'" >'.$opt['Name'].'</label>'."\n";
        }
        return $this->getLabel($prefix).$cb.'</br>';
    }

    private function getBasicProperties($prefix)
    {
        $bp = ' name="'.$prefix.$this->Name.'" id="'.$prefix.$this->Name.'" placeholder="'.$this->Placeholder.'" ';

        if ($this->Obligatoire)
        {
            $bp.=' required="required" ';
        }

        if ($this->Locked)
        {
            $bp.=' readonly="readonly" ';
        }

        return $bp;
    }

    private function getLabel($prefix)
    {
        return '<label for="'.$prefix.$this->Name.'">'.$this->Label.'</label></br>'."\n";
    }


    /**
     * 
     * VERIFICATIONS ET TRANSFORMATIONS
     * 
     */

    public static function buildOptionsArray(string $options_string)
    {
        if (mb_substr($options_string, -1) == ";")
        {
            $options_string = mb_substr($options_string, 0, -1);
        }
        
        $basic_options_array = explode(";", $options_string);

        $options = [];
        foreach($basic_options_array as $opt)
        {
            $options[] = ["Name" => $opt, "Value" => slugify($opt)];
        }

        return $options;
    }

    public static function optionsToString($options)
    {
        if (is_array($options))
        {   
            $string = "";
            foreach($options as $opt)
            {
                $string .= $opt['Name'].";";
            }
            return mb_substr($string, 0, -1);
        }
        else
        {
            return "NULL";
        }
    }

    public static function stringToType($string)
    {
        switch($string)
        {
            case "TEXT":
                return self::TYPE_TEXT;
                break;
            case "NUM":
                return self::TYPE_NUM;
                break;
            case "EMAIL":
                return self::TYPE_EMAIL;
                break;
            case "TEL":
                return self::TYPE_TEL;
                break;
            case "DATE":
                return self::TYPE_DATE;
                break;
            case "TIME":
                return self::TYPE_TIME;
                break;
            case "DT":
                return self::TYPE_DT;
                break;
            case "SELECT":
                return self::TYPE_SELECT;
                break;
            case "RADIO":
                return self::TYPE_RADIO;
                break;
            case "CHECKBOXES":
                return self::TYPE_CHECKBOXES;
                break;
            default:
                return self::TYPE_STRING;
                break;
        }
    }

    public function export()
    {
        return  base64_encode(json_encode(  [   'n' => $this->Name,
                                                'l' => $this->Label,
                                                'p' => $this->Placeholder,
                                                't' => $this->Type,
                                                'r' => $this->RegEx,
                                                'em' => $this->ErrorMsg,
                                                'nd' => $this->Obligatoire,
                                                'o' => $this->Options,
                                                'v' => $this->Value
                                            ]));
    }

    public static function import($e)
    {
        $e = json_decode(base64_decode($e), true);
        if  (isset($e['n'])
            && isset($e['l'])
            && isset($e['p'])
            && isset($e['t'])
            && (isset($e['r']) || is_null($e['r']))
            && isset($e['em'])
            && isset($e['nd'])
            && (isset($e['o']) || is_null($e['o']))
            && (isset($e['v']) || is_null($e['v'])))
        {
            return new self($e['n'], $e['l'], $e['p'], $e['t'], $e['nd'], $e['r'], $e['em'], $e['o'], $e['v']);
        }
        else
        {
            return NULL;
        }
    }

    public function exportToArray()
    {
        $array = [  'n' => $this->Name,
                    'l' => $this->Label,
                    'p' => $this->Placeholder,
                    't' => $this->Type,
                    'r' => $this->RegEx,
                    'em' => $this->ErrorMsg,
                    'nd' => $this->Obligatoire,
                    'o' => $this->Options,
                    'v' => $this->Value ];
        return $array;
    }

    public static function importFromArray($array)
    {
        if  (isset($array['n'])
            && isset($array['l'])
            && isset($array['p'])
            && isset($array['t'])
            && (isset($array['r']) || is_null($array['r']))
            && isset($array['em'])
            && isset($array['nd'])
            && (isset($array['o']) || is_null($array['o']))
            && (isset($array['v']) || is_null($array['v'])))
        {
            return new self($array['n'], $array['l'], $array['p'], $array['t'], $array['nd'], $array['r'], $array['em'], $array['o'], $array['v']);
        }
        else
        {
            return NULL;
        }
    }

}

?>