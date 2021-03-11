<?php

namespace App\Validation;

use App\Exceptions\ValidationException;

class BaseValidation
{

    /**
     * Contains data to be validated.
     * @var array
     */
    private $data = [];

    /**
     * Contains rules to be used when validating.
     * @var array
     */
    private $rules = [];

    /**
     * Get $this->data.
     * @return array
     */
    public function getData(){
        return $this->data;
    }

    /**
     * Set $this->data.
     * @param array $value Array of data to be set.
     */
    public function setData(array $value){
        $this->data = $value;
    }

    /**
     * Get $this->rules.
     * @return array
     */
    public function getRules(){
        return $this->rules;
    }

    /**
     * Set $this->rules.
     * @param array $value Array of rules to be set.
     */
    public function setRules(array $value){
        $this->rules = $value;
    }

    /**
     * Value that gets prefixed to array keys that are "converted".
     * @var string
     */
    public $arr_prefix = "_";

    /**
     * Runs the validation check.
     * Replaces all numerical keys of $this->data with strings so that CodeIgnitier4 validation can process them correctly.
     * Attempts to create any dynamic rules (for address lines and line items).
     * Creates new instance of CodeIgnitier4's validation service and sets required data before running the validation.
     * If no exception is thrown, then the validation has passed.
     * @throws ValidationException if the validation fails.
     */
    public function validate()
    {

        // run some data manipulation methods...
        $this->data = $this->replaceArrayKeys($this->data); // replace any numerical keys in array with a string version
        $this->createRulesForAddressLines();                // adds to $this->rules if address lines are present
        $this->createRulesAndErrorsForLineItems();          // adds to $this->rules if line items are present

        // create instance of CI4 validator
        $validation = \Config\Services::validation();

        // reset the validation instance
        $validation->reset();

        // set rules
        $validation->setRules($this->rules);

        // run validation on $data
        $validation->run($this->data);

        // check if there are any errors and throw them!
        if(!empty($validation->getErrors())){

            // throw a ValidationException with errors attached
            $exception = new ValidationException();
            $exception->setErrors($validation->getErrors());
            throw $exception;

        }

    }

    /**
     * Recursively replace numerical keys in array.
     * Loops through array and replaces any keys that return true when is_int() is used.
     * The method uses its self recursively when a elements value is an array.
     * @param  array $data Array to be processed.
     * @return array
     */
    private function replaceArrayKeys(array $data)
    {

        // new array that will hold our new data in
        $new_data = [];

        // loop through array
        foreach($data as $k => $v){

            // if the value is an array, we need to go one level deeper
            // we do this by repeating this method using the new array
            if(is_array($v)){

                // if the key is an int, we need to change it!
                if(is_int($k)){
                    $new_data[$this->arr_prefix.$k] = $this->replaceArrayKeys($v);
                // otherwise just add it as is
                }else{
                    $new_data[$k] = $this->replaceArrayKeys($v);
                }

            }else{

                // if the key is an int, we need to change it!
                if(is_int($k)){
                    $new_data[$this->arr_prefix.$k] = $v;
                // otherwise just add it as is
                }else{
                    $new_data[$k] = $v;
                }

            }

        }

        // return the $new_data
        return $new_data;

    }

    /**
     * Adds additional $this->rules if $this->data contains any "address_lines" or "invoice_address_lines" keys.
     */
    private function createRulesForAddressLines()
    {

        // how many address lines do we want maximum
        $max_lines = 4;

        // populate address lines rules
        if(isset($this->data['address_lines'])){
            $count = 0;
            foreach($this->data['address_lines'] as $k => $v){

                $this->rules['address_lines.'.$k] = 'required|max_length[35]';

                // we only want $max_lines set of rules created
                $count++;
                if($count == $max_lines){
                    break;
                }

            }
        }

        // populate invoice address lines rules
        if(isset($this->data['invoice_address_lines'])){
            $count = 0;
            foreach($this->data['invoice_address_lines'] as $k => $v){

                $this->rules['invoice_address_lines.'.$k] = 'required|max_length[35]';

                // we only want $max_lines set of rules created
                $count++;
                if($count == $max_lines){
                    break;
                }

            }
        }

    }

    /**
     * Adds additional $this->rules if $this->data contains any "line_items" keys.
     * @todo Add validation for all possible allowed combinations of line items.
     */
    private function createRulesAndErrorsForLineItems()
    {

        // just some notes on what i want these rules to accomplish
        // i need part_code or text, but not both
        // i always need a quantity

        // if no line_items are set, don't bother making the rules
        if(!isset($this->data['line_items'])){
            return;
        }

        // for each line item
        foreach($this->data['line_items'] as $k => $v){

            // what line are we on, used for the error messages
            $line = (ltrim($k, $this->arr_prefix)+1);

            // error, we don't want part_code and text in same line
            if(isset($v['part_code']) && isset($v['text'])){

                // will cause an error within the validation
                $error_message = "Line " . $line . " can not have a part_code and text value.";
                $this->rules['part_code_and_text'] = [
                    'rules' => 'required|integer|alpha',
                    'errors' => [
                        'required' => $error_message,
                        'integer' => $error_message,
                        'alpha' => $error_message,
                    ]
                ];

                // don't add any other rules
                continue;

            }

            // error, we need either part_code or text
            if(!isset($v['part_code']) && !isset($v['text'])){

                // will cause an error within the validation
                $error_message = "Line " . $line . " must include a part_code or text value.";
                $this->rules['part_code_and_text'] = [
                    'rules' => 'required|integer|alpha',
                    'errors' => [
                        'required' => $error_message,
                        'integer' => $error_message,
                        'alpha' => $error_message,
                    ]
                ];

                // don't add any other rules
                continue;

            }

            // if part_code exists in the post data, lets make sure it passes our validation rules
            if(isset($v['part_code'])){
                // rules for "part_code
                $this->rules["line_items.".$k.".part_code"] = [
                    'rules' => 'required|exact_length[7]|regex_match[/[A-Z]\d{6}/]',
                    'errors' => [
                        'required' => "Line " . $line . " must include a part_code.",
                        'exact_length' => "Line " . $line . " part_code must be 7 characters.",
                        'regex_match' => "Line " . $line . " part_code must conform to the pattern of A000000."
                    ]
                ];
            }

            if(isset($v['text'])){
                // rules for "text
                $this->rules["line_items.".$k.".text"] = [
                    'rules' => 'required|max_length[70]',
                    'errors' => [
                        'required' => "Line " . $line . " must include a text value.",
                        'max_length' => "Line " . $line . " text value must be less than 70 characters.",
                    ]
                ];
            }

            // rules for "quantity"
            $this->rules["line_items.".$k.".quantity"] = [
                'rules' => 'required|integer|greater_than[0]|less_than[100]',
                'errors' => [
                    'required' => "Line " . $line . " must include a quantity value.",
                    'integer' => "Line " . $line . " quantity value must be numeric.",
                    'greater_than' => "Line " . $line . " quantity value must be at least 1.",
                    'less_than' => "Line " . $line . " quantity value must be less than 100.",
                ]
            ];

        }

    }

}
