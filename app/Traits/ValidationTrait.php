<?php

namespace App\Traits;

use App\Models\Kerridge\BaseKerridgeAbstract;
use App\Exceptions\ClassNotFoundException;

Trait ValidationTrait
{

    /**
     * Runs the validation check.
     * Will skip validation check if the supplied $model wants them to be skipped.
     * Forwards supplied $data to the Validation class specified in $model, and then runs the validation.
     * @param  BaseKerridgeAbstract $model Instance of a class that extends BaseKerridgeAbstract.
     * @param  array                $data  Array of data that needs to be validated.
     * @throws ClassNotFoundException if the validation class can't be found.
     * @throws ValidationException if the validation check fails.
     */
	public function runValidation(BaseKerridgeAbstract $model, array $data)
	{

        // skip validation if the $model->validation_file is ""
        if($model->validation_file == ""){
            return;
        }

        // get validation class
        $validation_class = $this->getValidationClass($model->validation_file);

        // set data on validation class
        $validation_class->setData($data);

        // run the validation logic
        $validation_class->validate();

	}

    /**
     * Get validation class.
     * @param $validation_file Name of the validation file.
     * @return BaseValidation
     * @throws ClassNotFoundException if class can't be found.
     */
    private function getValidationClass(string $validation_file)
    {

        // create reference to validation file.
        $validation = "App\\Validation\\".$validation_file."Validation";

        // make sure the $this->validation class actually exists
        if(!class_exists($validation)){
            throw new ClassNotFoundException($validation);
        }

        // create instance of the validation class
        $class = new $validation();

        // return the class
        return $class;

    }

}