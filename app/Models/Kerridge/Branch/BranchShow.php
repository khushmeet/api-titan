<?php

namespace App\Models\Kerridge\Branch;

use App\Models\Kerridge\BaseKerridgeAbstract;
use App\Models\Kerridge\Branch\BranchShow;

class BranchShow extends BaseKerridgeAbstract
{

    ///////////////////////////////////
    // OVERWRITE PROTECTED VARIABLES //
    ///////////////////////////////////

    public $capacity = 10;
    public $cache_duration = 100;
    public $url_structure = "branches";
    public $no_entities_message = 'no branches found';
    public $file_name = 'Branch/branchRequest';
    public $validation_file = 'Branch\\BranchShow';

    /**
     * Get and modify XML from a template file.
     * @return SimpleXMLElement
     */
    public function fetch(array $data)
    {

        // create variables from associative array passed in
        $branch_code = $data['branch_code'];

        // attempt to load in xml file
        $xml = self::getXMLFromFile($this->file_name);

        ///////////////////////////////////////////////
        // MODIFY THE XML TEMPLATE TO SUIT OUT NEEDS //
        ///////////////////////////////////////////////

        // add branch number to xml
        $xml->body->branchRequest["branch"] = $branch_code;

        // return the xml
        return $xml;

    }

    /**
     * Modify SimpleXMlElement into an array.
     * @param SimpleXMLElement $response
     * @return array
     */
    public function toData(\SimpleXMLElement $response)
    {

        // use same logic as BranchIndex
        $branch_index = new BranchIndex();
        $data = $branch_index->toData($response);

        // return data
        return $data;

    }

}
