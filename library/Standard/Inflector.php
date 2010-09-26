<?php

/**
 * A class with static methods to convert and inflect strings
 *
 * @author Lee Parker
 **/
class Standard_Inflector 
{
    /**
     * Converts a string with _ to CamelCase
     *
     * @param string $text
     * @return string
     * @author Lee Parker
     **/
    static public function toCamelCase($text, $separator = "_")
    {
        $parts = explode($separator, $text);
        
        foreach($parts as $key => $value)
        {
            $parts[$key] = ucfirst(strtolower($value));
        }
        
        return implode('', $parts);
    }
    
    /**
     * Converts a string with _ to lowerCamelCase
     *
     * @param string $text
     * @return string
     * @author Lee Parker
     **/
    static public function toLowerCamelCase($text, $separator = "_")
    {
        $parts = explode($separator, $text);
        
        foreach($parts as $key => $value)
        {
            $new = strtolower($value);
            
            if($key > 0)
            {
                $new = ucfirst($new);
            }
            
            $parts[$key] = $new;
        }
        
        return implode('', $parts);
    }
}