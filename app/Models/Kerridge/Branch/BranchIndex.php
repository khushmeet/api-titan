<?php

namespace App\Models\Kerridge\Branch;

use App\Services\SimpleXMLToArrayService;
use App\Models\Kerridge\BaseKerridgeAbstract;

class BranchIndex extends BaseKerridgeAbstract
{

    ///////////////////////////////////
    // OVERWRITE PROTECTED VARIABLES //
    ///////////////////////////////////

    public $capacity = 10;
    public $cache_duration = 100;
    public $url_structure = "branches";
    public $no_entities_message = 'no branches found';
    public $file_name = 'Branch/branchRequest';
    public $validation_file = '';

    /**
     * Get and modify XML from a template file.
     * @return SimpleXMLElement
     */
    public function fetch($empty = null)
    {

        // attempt to load in xml file
        $xml = $this->getXMLFromFile($this->file_name);

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

        // create class
        $simple_xml_to_array_service = new SimpleXMlToArrayService();

        // convert to array
        $array = $simple_xml_to_array_service->execute($response->body->branchResponse);

        // create data
        $data = [
            'branches' => $array['branch_response']['branches'],
            // 'cache_time' => $array["branch_response"]["cache_time"],
        ];

        // add count to data
        $this->addCountToData($data, 'branches');

        // change any reference to "number" in the "branch" object to "branch_id"
        foreach($data["branches"] as &$branch){
            $branch["branch_id"] = $branch["number"];
            unset($branch["number"]);
        }

        // return data
        return $data;

    }

}
