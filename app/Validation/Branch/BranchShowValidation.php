<?php

namespace App\Validation\Branch;

use App\Validation\BaseValidation;

/**
 * This class acts as a location to set validation rules.
 */
class BranchShowValidation extends BaseValidation
{

    /**
     * Sets rules for BaseValidation.
     */
    public function __construct()
    {

        $this->setRules([
            'branch_code' => 'required|numeric|exact_length[4]',
        ]);

    }

}
