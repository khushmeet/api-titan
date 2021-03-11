<?php

namespace App\Controllers;

class BranchController extends BaseAPIController
{

    /**
     * Display information on all branches.
     */
    public function index()
    {

        // get "Kerridge" model relative to this controller route
        $model = new \App\Models\Kerridge\Branch\BranchIndex();

        // use the "default execution"
        $this->defaultExecution($model);

    }

    /**
     * Display information on one branch.
     * @param string $branch_code Number of the branch we want information on.
     */
    public function show(string $branch_code)
    {

        // get "Kerridge" model relative to this controller route
        $model = new \App\Models\Kerridge\Branch\BranchShow();

        // use the "default execution"
        $this->defaultExecution($model, [
            'branch_code' => $branch_code,
        ]);

    }

}
