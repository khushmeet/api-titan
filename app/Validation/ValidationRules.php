<?php

namespace App\Validation;

/**
 * Contains custom validation rules for us with CodeIgnitier4.
 */
class ValidationRules
{

    /**
     * Assert that $value is an even number.
     * @param  string      $value
     * @param  string|null &$error
     * @return boolean
     */
    public function even(string $value, string &$error = null)
    {

        if ((int)$value % 2 == 0){
            return true;
        }

        return false;

    }

    /**
     * Assert that $array has less than $param elements.
     * @param  array       $array  Array to be checked.
     * @param  string      $param  Maximum allowed array size.
     * @param  array       $data
     * @param  string|null &$error
     * @return boolean
     */
    public function max_array_size(array $array, string $param, array $data = [], string &$error = null)
    {

        if(count($array) > $param){
            return false;
        }

        return true;

    }

    /**
     * Assert that $array has at least $param elements.
     * @param  array       $array  Array to be checked.
     * @param  string      $param  Minimum allowed array size.
     * @param  array       $data
     * @param  string|null &$error
     * @return boolean
     */
    public function min_array_size(array $array, string $param, array $data = [], string &$error = null)
    {

        if(count($array) < $param){
            return false;
        }

        return true;

    }

    /**
     * Assert that $variable is an array.
     * @param  mixed       $variable
     * @param  string|null &$error
     * @return boolean
     */
    public function valid_array($variable, string &$error = null)
    {

        if(!is_array($variable)){
            return false;
        }

        return true;

    }
}